<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    /**
     * Verified Stripe webhook. Reconciles order state with Stripe for events that
     * may happen out of band with our synchronous checkout — a charge captured
     * async, or (most usefully) a refund issued from the Stripe dashboard.
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature') ?? '',
                (string) config('services.stripe.webhook.secret')
            );
        } catch (SignatureVerificationException|UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $charge = $event->data->object;

        match ($event->type) {
            'charge.succeeded' => $this->markPaid($charge),
            'charge.failed' => $this->markFailed($charge),
            'charge.refunded' => $this->reconcileRefund($charge),
            default => null, // acknowledge and ignore everything else
        };

        return response()->json(['received' => true]);
    }

    private function orderForCharge($charge): ?Order
    {
        return isset($charge->id) ? Order::where('transaction_id', $charge->id)->first() : null;
    }

    private function markPaid($charge): void
    {
        $this->transition($this->orderForCharge($charge), Order::STATUS_PAID, 'Stripe webhook: charge.succeeded');
    }

    private function markFailed($charge): void
    {
        $this->transition($this->orderForCharge($charge), Order::STATUS_FAILED, 'Stripe webhook: charge.failed');
    }

    private function reconcileRefund($charge): void
    {
        $order = $this->orderForCharge($charge);
        if (! $order) {
            return;
        }

        // Stripe's amount_refunded (in cents) is authoritative. Setting it (rather
        // than incrementing) keeps this idempotent across webhook retries and
        // refunds already recorded in-app.
        $refunded = (float) (($charge->amount_refunded ?? 0) / 100);
        $fully = $refunded >= (float) $order->total_amount;

        $order->update([
            'refund_total' => $refunded,
            'fully_refunded' => $fully,
            'partially_refunded' => ! $fully && $refunded > 0,
        ]);

        $this->transition($order, $fully ? Order::STATUS_REFUNDED : Order::STATUS_PARTIALLY_REFUNDED, 'Stripe webhook: charge.refunded');
    }

    /**
     * Move the order through the state machine only if the transition is legal —
     * webhooks are at-least-once and may arrive for an order already in the target
     * (or a later) state, so an illegal transition is silently skipped, never thrown.
     */
    private function transition(?Order $order, string $status, string $notes): void
    {
        if ($order && in_array($status, Order::TRANSITIONS[$order->status] ?? [], true)) {
            $order->transitionTo($status, notes: $notes);
        }
    }
}
