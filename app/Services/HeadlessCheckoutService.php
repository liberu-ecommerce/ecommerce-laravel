<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Factories\PaymentGatewayFactory;
use App\Models\CartItem;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Places an order from a user's persistent (CartItem) cart for headless/API clients —
 * the GraphQL checkout mutation's engine. Reuses the same money-safe mechanics as the
 * web checkout: stock is reserved with a guarded atomic decrement inside a transaction
 * BEFORE charging, and shipping is billed from the STORED shipping quote (never a
 * client-supplied price — the #775 quote-trust property).
 *
 * v1 is intentionally lean: no coupon, dropship, or downloadable-link handling. Those
 * live in the web checkout and can be lifted into a shared service later.
 */
class HeadlessCheckoutService
{
    public function __construct(
        private ShippingService $shippingService,
        private TaxCalculator $taxCalculator,
    ) {}

    public function place(User $user, array $input): Order
    {
        $cart = CartItem::where('user_id', $user->id)->with('products')->get();
        if ($cart->isEmpty()) {
            throw new CheckoutException('Your cart is empty.');
        }

        $paymentMethod = $input['paymentMethod'] ?? 'stripe';
        $country = strtoupper((string) ($input['country'] ?? ''));

        $subtotal = (float) $cart->sum(fn (CartItem $i) => (float) $i->price * $i->quantity);
        [$shippingCost, $carrier, $service, $quoteId] = $this->resolveShipping($input, $user);
        [$taxAmount, $taxLines] = $this->calculateTax($cart, $input, $shippingCost);

        $total = max(0, round($subtotal + $shippingCost + $taxAmount, 2));

        $order = null;
        DB::transaction(function () use (&$order, $cart, $user, $paymentMethod, $country, $total, $shippingCost, $carrier, $service, $quoteId, $taxAmount, $taxLines) {
            $order = Order::create([
                'user_id' => $user->id,
                'customer_email' => $user->email,
                'billing_country' => $country,
                'shipping_carrier' => $carrier,
                'shipping_service' => $service,
                'shipping_quote_id' => $quoteId,
                'payment_method' => $paymentMethod,
                'total_amount' => $total,
                'shipping_cost' => $shippingCost,
                'tax_amount' => $taxAmount,
                'tax_lines' => $taxLines,
                'status' => Order::STATUS_PENDING,
            ]);

            foreach ($cart as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);

                $before = Product::where('id', $item->product_id)->value('inventory_count');

                // Atomic guarded decrement: succeeds only while enough stock remains.
                $affected = Product::where('id', $item->product_id)
                    ->where('inventory_count', '>=', $item->quantity)
                    ->decrement('inventory_count', $item->quantity);

                if ($affected === 0) {
                    throw new CheckoutException('Some items in your cart are no longer available in the requested quantity.');
                }

                InventoryLog::create([
                    'product_id' => $item->product_id,
                    'quantity_change' => -$item->quantity,
                    'old_quantity' => $before,
                    'new_quantity' => $before - $item->quantity,
                    'reason' => 'order',
                    'reference_id' => $order->id,
                    'reference_type' => Order::class,
                ]);
            }
        });

        /** @var Order $order */
        $this->charge($order, $cart, $paymentMethod, $input['stripeToken'] ?? null, $total);

        $order->transitionTo(Order::STATUS_PAID, notes: 'Payment captured (headless checkout)');

        CartItem::where('user_id', $user->id)->delete();

        return $order;
    }

    /** @return array{0: float, 1: ?string, 2: ?string, 3: ?int} */
    private function resolveShipping(array $input, User $user): array
    {
        if (empty($input['shippingQuoteId'])) {
            // No live rate selected — never accept a client-supplied amount, so 0.
            return [0.0, null, null, null];
        }

        // Scope by the user's id (headless clients have no session). resolveQuote also
        // rejects an expired quote, so the stored amount is always current.
        $quote = $this->shippingService->resolveQuote((int) $input['shippingQuoteId'], '', $user->id);
        if ($quote === null) {
            throw new CheckoutException('Your selected shipping rate is no longer valid. Please re-fetch rates.');
        }

        return [(float) $quote->amount, $quote->carrier, $quote->service, $quote->id];
    }

    /** @return array{0: float, 1: array} */
    private function calculateTax(Collection $cart, array $input, float $shippingCost): array
    {
        $address = [
            'country' => $input['country'] ?? null,
            'state' => $input['state'] ?? null,
            'city' => $input['city'] ?? null,
            'postal_code' => $input['postalCode'] ?? null,
        ];

        $taxItems = [];
        foreach ($cart as $item) {
            if ($item->products) {
                $taxItems[] = ['product' => $item->products, 'quantity' => $item->quantity, 'price' => (float) $item->price];
            }
        }

        $result = $this->taxCalculator->calculateCartTax($taxItems, $address, $shippingCost);

        return [$result['total'], $result['lines']];
    }

    private function charge(Order $order, Collection $cart, string $paymentMethod, ?string $token, float $total): void
    {
        if ($total <= 0) {
            return;
        }

        $result = PaymentGatewayFactory::create($paymentMethod)->processPayment($total, [
            'order_id' => $order->id,
            'customer_email' => $order->customer_email,
            'token' => $token,
        ]);

        if (! ($result['success'] ?? false)) {
            $this->releaseInventory($order, $cart);
            $order->transitionTo(Order::STATUS_FAILED, notes: 'Payment failed: '.($result['error'] ?? 'unknown'));

            throw new CheckoutException('Payment failed: '.($result['error'] ?? 'please try again.'));
        }

        if (isset($result['transaction_id'])) {
            $order->update(['transaction_id' => $result['transaction_id']]);
        }
    }

    /** Return reserved stock when a charge fails after the order was created. */
    private function releaseInventory(Order $order, Collection $cart): void
    {
        foreach ($cart as $item) {
            $before = Product::where('id', $item->product_id)->value('inventory_count');
            if ($before === null) {
                continue;
            }

            Product::where('id', $item->product_id)->increment('inventory_count', $item->quantity);

            InventoryLog::create([
                'product_id' => $item->product_id,
                'quantity_change' => $item->quantity,
                'old_quantity' => $before,
                'new_quantity' => $before + $item->quantity,
                'reason' => 'payment_failed_release',
                'reference_id' => $order->id,
                'reference_type' => Order::class,
            ]);
        }
    }
}
