<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\AdminLogin;
use App\Http\Middleware\AuthenticateAdminPanel;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
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
        $middleware = [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            PreventRequestForgery::class,
            SubstituteBindings::class,
        ];

        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(AdminLogin::class)
            ->sidebarWidth('10rem')
            ->collapsedSidebarWidth('4rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigationItems([
                NavigationItem::make('客服工作台')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->url('/stream-chat-agent', shouldOpenInNewTab: true)
                    ->sort(21),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->middleware($middleware)
            ->authMiddleware([
                AuthenticateAdminPanel::class,
            ]);

        return $panel;
    }
}
