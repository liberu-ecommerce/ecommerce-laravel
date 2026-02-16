<?php

namespace App\Providers\Filament;

use Filament\Widgets\AccountWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use App\Filament\App\Pages;
use App\Filament\App\Pages\EditProfile;
use App\Http\Middleware\TeamsPermission;
use App\Models\Team;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages as FilamentPage;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Jetstream;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // ->login([AuthenticatedSessionController::class, 'create'])
            // ->passwordReset()
            // ->emailVerification()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::Gray,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Profile')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn () => $this->shouldRegisterMenuItem()
                        ? url(EditProfile::getUrl())
                        : url($panel->getPath())),
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->pages([
                FilamentPage\Dashboard::class,
                EditProfile::class,
                // Pages\ApiTokenManagerPage::class,
            ])->widgets([
                AccountWidget::class,
                \App\Filament\Admin\Widgets\SalesOverviewWidget::class,
                \App\Filament\Admin\Widgets\SalesTrendsChart::class,
                \App\Filament\Admin\Widgets\TopProductsWidget::class,
                \App\Filament\Admin\Widgets\CustomerDemographicsWidget::class,
                \App\Filament\Admin\Widgets\LowStockInventoryWidget::class,
                \App\Filament\Admin\Widgets\RecentOrdersWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                TeamsPermission::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
            ]);

        // if (Features::hasApiFeatures()) {
        //     $panel->userMenuItems([
        //         MenuItem::make()
        //             ->label('API Tokens')
        //             ->icon('heroicon-o-key')
        //             ->url(fn () => $this->shouldRegisterMenuItem()
        //                 ? url(Pages\ApiTokenManagerPage::getUrl())
        //                 : url($panel->getPath())),
        //     ]);
        // }

        if (Features::hasTeamFeatures()) {
            // $panel
            //     ->tenant(Team::class, ownershipRelationship: 'team')
            //     ->tenantRegistration(Pages\CreateTeam::class)
            //     ->tenantProfile(Pages\EditTeam::class)
            //     ->userMenuItems([
            //         MenuItem::make()
            //             ->label('Team Settings')
            //             ->icon('heroicon-o-cog-6-tooth')
            //             ->url(fn () => $this->shouldRegisterMenuItem()
            //                 ? url(Pages\EditTeam::getUrl())
            //                 : url($panel->getPath())),
            //     ]);
        }

        return $panel;
    }

    public function boot()
    {
       
    }

    public function shouldRegisterMenuItem(): bool
    {
        return true; //auth()->user()?->hasVerifiedEmail() && Filament::hasTenancy() && Filament::getTenant();
    }
}
