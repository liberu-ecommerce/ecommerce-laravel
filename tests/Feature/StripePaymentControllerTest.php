<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripePaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_one_time_payment_requires_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/stripe/payment', [
            'payment_method' => 'pm_test',
        ]);

        $response->assertStatus(422);
    }

    public function test_create_one_time_payment_requires_payment_method(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/stripe/payment', [
            'amount' => 50.00,
        ]);

        $response->assertStatus(422);
    }

    public function test_create_subscription_requires_plan(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/stripe/subscription', []);

        $response->assertStatus(422);
    }

    public function test_update_subscription_requires_subscription_id_and_plan(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patchJson('/stripe/subscription', [
            'plan' => 'premium',
        ]);

        $response->assertStatus(422);
    }

    public function test_cancel_subscription_requires_subscription_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson('/stripe/subscription', []);

        $response->assertStatus(422);
    }
}
