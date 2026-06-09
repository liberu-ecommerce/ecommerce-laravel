<?php

namespace Tests\Unit;

use App\Models\GiftRegistry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftRegistryModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeRegistry(array $overrides = []): GiftRegistry
    {
        $user = User::factory()->create();
        return GiftRegistry::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Wedding Registry',
            'type' => 'wedding',
            'event_date' => now()->addMonths(3)->toDateString(),
            'privacy' => 'public',
            'is_active' => true,
        ], $overrides));
    }

    public function test_gift_registry_can_be_created(): void
    {
        $registry = $this->makeRegistry();

        $this->assertInstanceOf(GiftRegistry::class, $registry);
        $this->assertEquals('Wedding Registry', $registry->name);
    }

    public function test_slug_auto_generated(): void
    {
        $registry = $this->makeRegistry(['slug' => null]);

        $this->assertNotNull($registry->slug);
        $this->assertStringContainsString('wedding-registry', $registry->slug);
    }

    public function test_private_registry_gets_access_code(): void
    {
        $registry = $this->makeRegistry(['privacy' => 'private', 'access_code' => null]);

        $this->assertNotNull($registry->access_code);
        $this->assertEquals(8, strlen($registry->access_code));
    }

    public function test_public_registry_has_no_auto_access_code(): void
    {
        $registry = $this->makeRegistry(['privacy' => 'public']);

        $this->assertNull($registry->access_code);
    }

    public function test_is_active_is_boolean_cast(): void
    {
        $registry = $this->makeRegistry(['is_active' => true]);

        $this->assertIsBool($registry->fresh()->is_active);
    }

    public function test_event_date_is_date_cast(): void
    {
        $registry = $this->makeRegistry();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $registry->fresh()->event_date);
    }

    public function test_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $registry = GiftRegistry::create([
            'user_id' => $user->id,
            'name' => 'Baby Shower',
            'type' => 'baby',
            'event_date' => now()->addMonths(2)->toDateString(),
            'privacy' => 'public',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(User::class, $registry->user);
        $this->assertEquals($user->id, $registry->user->id);
    }
}
