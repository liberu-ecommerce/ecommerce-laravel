<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReturnRequestReadApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::findOrCreate('super_admin', 'web');

        return User::factory()->create()->assignRole('super_admin');
    }

    private function returnFor(User $user, string $status = 'pending'): ReturnRequest
    {
        $order = Order::create([
            'user_id' => $user->id,
            'customer_email' => $user->email,
            'total_amount' => 50,
            'status' => Order::STATUS_PAID,
        ]);

        return ReturnRequest::create([
            'order_id' => $order->id,
            'customer_id' => $user->id,
            'reason' => 'defective',
            'status' => $status,
        ]);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/returns')->assertStatus(401);
    }

    public function test_customer_lists_only_their_own_returns(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $this->returnFor($me);
        $this->returnFor($me);
        $this->returnFor($other);

        Sanctum::actingAs($me);
        $response = $this->getJson('/api/returns');

        $response->assertStatus(200);
        $this->assertSame(2, $response->json('total'));
        $customerIds = collect($response->json('data'))->pluck('customer_id')->unique()->values()->all();
        $this->assertSame([$me->id], $customerIds);
    }

    public function test_admin_lists_all_returns_and_can_filter_by_status(): void
    {
        $this->returnFor(User::factory()->create(), 'pending');
        $this->returnFor(User::factory()->create(), 'approved');

        Sanctum::actingAs($this->admin());

        $this->getJson('/api/returns')->assertStatus(200)->assertJsonPath('total', 2);
        $this->getJson('/api/returns?status=approved')->assertStatus(200)->assertJsonPath('total', 1);
    }

    public function test_customer_can_view_own_return(): void
    {
        $me = User::factory()->create();
        $return = $this->returnFor($me);
        Sanctum::actingAs($me);

        $this->getJson("/api/returns/{$return->id}")->assertStatus(200)->assertJsonPath('id', $return->id);
    }

    public function test_customer_cannot_view_another_users_return(): void
    {
        $me = User::factory()->create();
        $return = $this->returnFor(User::factory()->create());
        Sanctum::actingAs($me);

        $this->getJson("/api/returns/{$return->id}")->assertStatus(404);
    }

    public function test_admin_can_view_any_return(): void
    {
        $return = $this->returnFor(User::factory()->create());
        Sanctum::actingAs($this->admin());

        $this->getJson("/api/returns/{$return->id}")->assertStatus(200);
    }

    public function test_admin_marks_a_return_received(): void
    {
        $return = $this->returnFor(User::factory()->create(), 'approved');
        Sanctum::actingAs($this->admin());

        $this->postJson("/api/returns/{$return->id}/received")->assertStatus(200);

        $this->assertSame('received', $return->fresh()->status);
        $this->assertNotNull($return->fresh()->received_at);
    }

    public function test_non_admin_cannot_mark_a_return_received(): void
    {
        $return = $this->returnFor(User::factory()->create(), 'approved');
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/returns/{$return->id}/received")->assertStatus(403);
        $this->assertSame('approved', $return->fresh()->status);
    }
}
