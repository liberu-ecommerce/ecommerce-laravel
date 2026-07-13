<?php

namespace Tests\Feature\Filament;

use App\Filament\App\Resources\Articles\Pages\ListArticles;
use App\Filament\App\Resources\Collections\Pages\ListCollections;
use App\Filament\App\Resources\Customers\Pages\ListCustomers;
use App\Filament\App\Resources\Groups\Pages\ListGroups;
use App\Filament\App\Resources\Invoices\Pages\ListInvoices;
use App\Filament\App\Resources\Orders\Pages\ListOrders;
use App\Filament\App\Resources\ProductRatings\Pages\ListProductRatings;
use App\Filament\App\Resources\ProductReviews\Pages\ListProductReviews;
use App\Filament\App\Resources\Products\Pages\ListProducts;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The /app Filament panel is Team-tenant-scoped (ownershipRelationship: 'team').
 * Every resource model therefore needs a team() relationship and a team_id column,
 * and the tenant owner must be able to access the tenant. These tests mount each
 * resource's list page under a tenant to prove the panel is wired end to end.
 */
class AppPanelTenancyTest extends TestCase
{
    use RefreshDatabase;

    private function actingInTeamPanel(): Team
    {
        // Allow all authorization so these tests isolate the TENANCY wiring, not the
        // Shield permission layer (which gates resources on the admin panel separately).
        Gate::before(fn () => true);
        Role::findOrCreate('super_admin', 'web');
        $user = User::factory()->withPersonalTeam()->create()->assignRole('super_admin');
        $team = $user->ownedTeams()->first();
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($team);

        return $team;
    }

    public function test_team_owner_can_access_their_tenant(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->ownedTeams()->first();

        $this->assertTrue($user->canAccessTenant($team));
        $this->assertTrue($user->getTenants(Filament::getPanel('app'))->contains($team));
    }

    public function test_every_app_panel_list_page_mounts(): void
    {
        $this->actingInTeamPanel();

        $pages = [
            ListOrders::class, ListProducts::class, ListCustomers::class,
            ListCollections::class, ListGroups::class, ListProductReviews::class,
            ListProductRatings::class, ListInvoices::class, ListArticles::class,
        ];

        foreach ($pages as $page) {
            Livewire::test($page)->assertSuccessful();
        }
    }
}
