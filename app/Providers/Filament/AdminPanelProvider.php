<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    /** How often open admin lists check for changes other people made. */
    public const LIST_REFRESH = '5s';

    /** Dashboard figures change slowly; no need to check as often. */
    public const DASHBOARD_REFRESH = '30s';

    public function boot(): void
    {
        // Every list (and the lists inside a record, like a shipment's items)
        // picks up other people's changes without a page refresh.
        Table::configureUsing(fn (Table $table) => $table->poll(self::LIST_REFRESH));

        // Every date box gets a "Today" link beside its label.
        DatePicker::configureUsing(fn (DatePicker $picker) => $picker->hintAction(
            Action::make('today')
                ->label('Today')
                ->link()
                ->visible(fn () => ! $picker->isDisabled())
                ->action(function () use ($picker): void {
                    $picker->state(today()->toDateString());
                    $picker->callAfterStateUpdated();
                }),
        ));
    }

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
            ->databaseNotificationsPolling('10s')
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
