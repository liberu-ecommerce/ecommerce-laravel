<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::factory()->withPersonalTeam()->create();
    }

    private function makeOrder(User $user): Order
    {
        $customer = Customer::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $user->email,
            'phone_number' => '555-0001',
            'address' => '789 Elm St',
            'city' => 'Chicago',
            'state' => 'IL',
            'postal_code' => '60601',
        ]);
        return Order::create([
            'customer_id' => $customer->id,
            'customer_email' => $user->email,
            'total_amount' => 150.00,
            'order_date' => now()->toDateString(),
            'payment_status' => 'paid',
            'status' => 'completed',
            'shipping_status' => 'delivered',
        ]);
    }

    private function makeInvoice(Order $order): Invoice
    {
        return Invoice::forceCreate([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'invoice_date' => now(),
            'total_amount' => $order->total_amount ?? 0,
            'payment_status' => 'paid',
        ]);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get('/invoices');

        $response->assertRedirect('/login');
    }

    public function test_index_returns_view_for_authenticated_user(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);
        $this->makeInvoice($order);

        $response = $this->actingAs($user)->get('/invoices');

        $response->assertStatus(200);
        $response->assertViewIs('invoices.index');
        $response->assertViewHas('invoices');
    }

    public function test_show_requires_authentication(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);
        $invoice = $this->makeInvoice($order);

        $response = $this->get("/invoices/{$invoice->id}");

        $response->assertRedirect('/login');
    }

    public function test_show_returns_view_with_invoice(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);
        $invoice = $this->makeInvoice($order);

        $response = $this->actingAs($user)->get("/invoices/{$invoice->id}");

        $response->assertStatus(200);
        $response->assertViewIs('invoices.show');
        $response->assertViewHas('invoice');
    }

    public function test_show_returns_404_for_unknown_invoice(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get('/invoices/9999');

        $response->assertStatus(404);
    }

    public function test_index_supports_date_filter(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);
        $this->makeInvoice($order);

        $response = $this->actingAs($user)->get('/invoices?date=' . today()->toDateString());

        $response->assertStatus(200);
        $response->assertViewHas('invoices');
    }
}
