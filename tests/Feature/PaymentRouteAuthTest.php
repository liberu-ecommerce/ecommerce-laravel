<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Stripe payment and subscription routes act exclusively on the current user
 * ($request->user() / Auth::user()), so an unauthenticated request would deref
 * null and 500. They must require auth (a guest is redirected to login), matching
 * the PayPal subscription routes hardened alongside them.
 */
class PaymentRouteAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_stripe_payment_and_subscription_routes_require_auth(): void
    {
        $this->post(route('payment.create'))->assertRedirect(route('login'));
        $this->post(route('stripe.payment.create'))->assertRedirect(route('login'));
        $this->post(route('stripe.subscription.create'))->assertRedirect(route('login'));
        $this->patch(route('stripe.subscription.update'))->assertRedirect(route('login'));
        $this->delete(route('stripe.subscription.cancel'))->assertRedirect(route('login'));
    }

    public function test_subscription_management_routes_require_auth(): void
    {
        $this->post(route('subscription.create'))->assertRedirect(route('login'));
        $this->patch(route('subscription.change-plan'))->assertRedirect(route('login'));
        $this->delete(route('subscription.cancel'))->assertRedirect(route('login'));
    }

    public function test_auth_gate_is_transparent_to_a_logged_in_user(): void
    {
        // With auth satisfied the gate is a no-op: an empty body now reaches the
        // controller's own validation (422) instead of being blocked at the door.
        $this->actingAs(User::factory()->create())
            ->postJson(route('stripe.subscription.create'), [])
            ->assertStatus(422);
    }
}
