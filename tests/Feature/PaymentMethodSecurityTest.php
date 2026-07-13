<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function card(User $user, array $overrides = []): PaymentMethod
    {
        return PaymentMethod::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Visa',
            'details' => 'tok_'.uniqid(),
            'is_default' => false,
        ], $overrides));
    }

    public function test_endpoints_require_authentication(): void
    {
        $this->get('/payment_methods')->assertRedirect('/login');
        $this->post('/payment_methods/store', [])->assertRedirect('/login');
        $this->delete('/payment_methods/destroy/1')->assertRedirect('/login');
    }

    public function test_cannot_view_another_users_card(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $card = $this->card($a);

        $this->actingAs($b)->get("/payment_methods/edit/{$card->id}")->assertStatus(404);
    }

    public function test_cannot_delete_another_users_card(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $card = $this->card($a);

        $this->actingAs($b)->delete("/payment_methods/destroy/{$card->id}")->assertStatus(404);
        $this->assertDatabaseHas('payment_methods', ['id' => $card->id]);
    }

    public function test_cannot_update_another_users_card(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $card = $this->card($a, ['name' => 'Original']);

        $this->actingAs($b)->post("/payment_methods/update/{$card->id}", ['name' => 'Hacked'])->assertStatus(404);
        $this->assertDatabaseHas('payment_methods', ['id' => $card->id, 'name' => 'Original']);
    }

    public function test_add_binds_to_authenticated_user_ignoring_request_user_id(): void
    {
        $me = User::factory()->create();
        $victim = User::factory()->create();

        $this->actingAs($me)->post('/payment_methods/store', [
            'user_id' => $victim->id, 'name' => 'Visa', 'details' => 'tok_x',
        ])->assertStatus(201);

        $this->assertDatabaseHas('payment_methods', ['name' => 'Visa', 'user_id' => $me->id]);
        $this->assertDatabaseMissing('payment_methods', ['user_id' => $victim->id]);
    }

    public function test_index_lists_only_own_cards(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $this->card($me);
        $this->card($me);
        $this->card($other);

        $response = $this->actingAs($me)->getJson('/payment_methods');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json());
    }

    public function test_set_default_scopes_to_owner_and_unsets_siblings(): void
    {
        $me = User::factory()->create();
        $c1 = $this->card($me, ['is_default' => true]);
        $c2 = $this->card($me);

        $this->actingAs($me)->post("/payment_methods/set_default/{$c2->id}")->assertStatus(200);

        $this->assertTrue((bool) $c2->fresh()->is_default);
        $this->assertFalse((bool) $c1->fresh()->is_default);

        // Cannot set another user's card as default.
        $foreign = $this->card(User::factory()->create());
        $this->actingAs($me)->post("/payment_methods/set_default/{$foreign->id}")->assertStatus(404);
    }
}
