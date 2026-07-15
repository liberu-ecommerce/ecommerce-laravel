<?php

namespace Tests\Feature;

use App\Actions\Fortify\CreateNewUser;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Registration and /app access are one problem, not two.
 *
 * Registration was broken — CreateNewUser assigned a `staff` role that no seeder
 * creates (RolesSeeder dropped it in cf711cb without touching the caller), and on
 * an unseeded database Team::first() returned null and fatally errored first. That
 * bug was the only thing keeping strangers out of /app, because canAccessPanel
 * ended `return true; // TODO` and assignOrCreateTeam attached every registrant to
 * Team::first() — the merchant's own team.
 *
 * So fixing registration alone would have opened the door. These tests pin both
 * halves together: a registrant gets no team and no role, and /app requires a team.
 */
class AppPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.test',
            'password' => 'Sup3rSecret!Password',
            'password_confirmation' => 'Sup3rSecret!Password',
        ], $overrides);
    }

    #[Test]
    public function registration_succeeds(): void
    {
        $user = (new CreateNewUser)->create($this->payload());

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('ada@example.test', $user->email);
    }

    /**
     * The heart of it. A shopper signing up must not become a member of the
     * merchant's team — assignOrCreateTeam used to attach them to Team::first().
     */
    #[Test]
    public function registration_does_not_put_a_shopper_in_the_stores_team(): void
    {
        $storeTeam = Team::factory()->create(['name' => 'The Store', 'personal_team' => false]);

        $user = (new CreateNewUser)->create($this->payload());

        $this->assertSame(0, $user->allTeams()->count(), 'A registrant must not be attached to any team.');
        $this->assertFalse($user->belongsToTeam($storeTeam), 'A registrant must never land inside the store team.');
    }

    #[Test]
    public function registration_grants_no_roles(): void
    {
        $user = (new CreateNewUser)->create($this->payload());

        $this->assertCount(0, $user->getRoleNames(), 'A shopper is not staff.');
    }

    /** The gate that the registration bug was standing in for. */
    #[Test]
    public function a_user_with_no_team_cannot_reach_the_app_panel(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('app')));
    }

    #[Test]
    public function a_team_member_can_reach_the_app_panel(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($user);

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('app')));
    }

    #[Test]
    public function only_a_super_admin_can_reach_the_admin_panel(): void
    {
        Role::findOrCreate('super_admin', 'web');

        $plain = User::factory()->withPersonalTeam()->create();
        $this->actingAs($plain);
        $this->assertFalse($plain->canAccessPanel(Filament::getPanel('admin')));

        $admin = User::factory()->withPersonalTeam()->create()->assignRole('super_admin');
        $this->actingAs($admin);
        $this->assertTrue($admin->canAccessPanel(Filament::getPanel('admin')));
    }

    /**
     * The permission-backed resources deny a user who holds no permissions.
     *
     * Deliberately not asserted here: ArticleResource and CollectionResource have no
     * policy, and strictAuthorization is off, so Filament's authorization helper
     * returns allow() for them — a team member can CRUD their own tenant's articles
     * and collections (price included) without any permission check. That is a real
     * gap, but a separate change: the Shield permission set has no article_* or
     * collection_* entries at all, so adding the obvious policy would deny everyone
     * including super_admin and break the feature. It needs policies AND seeded
     * permissions together. Tracked, not smuggled in here.
     *
     * It is no longer reachable by a stranger either way — that took a team, and
     * registration no longer hands one out.
     */
    #[Test]
    public function permission_backed_resources_deny_a_user_without_permissions(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($user);

        foreach ([
            \App\Filament\App\Resources\Products\ProductResource::class,
            \App\Filament\App\Resources\Orders\OrderResource::class,
        ] as $resource) {
            $this->assertFalse($resource::canViewAny(), $resource.' must not be viewable without permission.');
            $this->assertFalse($resource::canCreate(), $resource.' must not be creatable without permission.');
            $this->assertFalse($resource::canDeleteAny(), $resource.' must not be deletable without permission.');
        }
    }
}
