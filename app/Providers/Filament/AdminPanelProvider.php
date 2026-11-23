<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // Move between admin pages without full reloads; links are prefetched on hover.
            ->spa(hasPrefetching: true)
            ->brandName('Yande Gadgets')
            // Sign-in happens through the main app (Fortify, with 2FA and passkeys).
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->navigationGroups(['Reports', 'Sales', 'Catalogue', 'Shipping', 'Buying', 'Finance', 'Settings', 'Help'])
            ->renderHook(PanelsRenderHook::STYLES_AFTER, fn () => view('filament.report-styles'))
            // Help menu next to the notifications bell, and the guided tours (resources/js/admin/tour.ts).
            ->renderHook(PanelsRenderHook::USER_MENU_BEFORE, fn () => view('filament.help-button'))
            ->renderHook(PanelsRenderHook::BODY_END, fn () => view('filament.tour'))
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
