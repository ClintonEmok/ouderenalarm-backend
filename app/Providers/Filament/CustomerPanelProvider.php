<?php

namespace App\Providers\Filament;

use App\Filament\Customer\Pages\CustomerDashboard;
use App\Filament\Customer\Widgets\CustomAccountWidget;
use App\Filament\Pages\Auth\Register;
use App\Livewire\AddressesProfileComponent;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class CustomerPanelProvider extends PanelProvider
{
//    TODO: Make custom register page (with assigning of role)
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('customer')
            ->path('customer')
            ->login()
//            ->registration(Register::class)
            ->passwordReset()
            ->databaseNotifications()
            ->sidebarFullyCollapsibleOnDesktop()
            ->colors([
                'primary' => '#3fa4f6',
            ])
            ->discoverResources(in: app_path('Filament/Customer/Resources'), for: 'App\\Filament\\Customer\\Resources')
            ->discoverPages(in: app_path('Filament/Customer/Pages'), for: 'App\\Filament\\Customer\\Pages')
            ->pages([
                CustomerDashboard::class
            ])
            ->discoverWidgets(in: app_path('Filament/Customer/Widgets'), for: 'App\\Filament\\Customer\\Widgets')
            ->widgets([
               CustomAccountWidget::class
//                Widgets\FilamentInfoWidget::class,
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
            ])->plugins([
                FilamentShieldPlugin::make(),
                BreezyCore::make()->myProfile(
                    userMenuLabel: 'Mijn Profiel', // Customizes the 'account' link label in the panel User Menu (default = null)
                    navigationGroup: 'Instellingen', // Sets the navigation group for the My Profile page (default = null)
                )->myProfileComponents([AddressesProfileComponent::class])]);

    }
}
