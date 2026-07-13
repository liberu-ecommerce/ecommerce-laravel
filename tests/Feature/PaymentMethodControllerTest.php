<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_payment_method_creates_record_for_the_authenticated_user(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        // user_id is derived from the session, not the request body.
        $response = $this->actingAs($user)->postJson('/payment_methods/store', [
            'name' => 'Visa',
            'details' => '****4242',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('payment_methods', [
            'user_id' => $user->id,
            'name' => 'Visa',
        ]);
    }

    public function test_add_payment_method_requires_name(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $response = $this->actingAs($user)->postJson('/payment_methods/store', [
            'details' => '****4242',
        ]);

        $response->assertStatus(422);
    }

    public function test_add_payment_method_requires_details(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $response = $this->actingAs($user)->postJson('/payment_methods/store', [
            'name' => 'Visa',
        ]);

        $response->assertStatus(422);
    }

    public function test_edit_payment_method_updates_own_record(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $pm = PaymentMethod::create([
            'user_id' => $user->id,
            'name' => 'Old Name',
            'details' => '****0000',
        ]);

        $response = $this->actingAs($user)->postJson("/payment_methods/update/{$pm->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('New Name', $pm->fresh()->name);
    }

    public function test_edit_payment_method_returns_404_when_not_found(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $response = $this->actingAs($user)->postJson('/payment_methods/update/9999', [
            'name' => 'New Name',
        ]);

        $response->assertStatus(404);
    }

    public function test_delete_payment_method_removes_own_record(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $pm = PaymentMethod::create([
            'user_id' => $user->id,
            'name' => 'Mastercard',
            'details' => '****1111',
        ]);

        $response = $this->actingAs($user)->deleteJson("/payment_methods/destroy/{$pm->id}");

        $response->assertStatus(200);
        $this->assertNull(PaymentMethod::find($pm->id));
    }

    public function test_delete_payment_method_returns_404_when_not_found(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $response = $this->actingAs($user)->deleteJson('/payment_methods/destroy/9999');

        $response->assertStatus(404);
    }
}
