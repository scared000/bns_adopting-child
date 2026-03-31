<?php

namespace App\Providers\Filament;

use App\Filament\Pages\ChildVisitDetail;
use App\Filament\Pages\ChildVisitLog;
use App\Filament\Resources\AdoptedChildren\Pages\ListFamilyProfiles;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
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
            ->brandName('Davao de Oro')
            ->login()
            ->registration()
            ->topbar(false)
            ->userMenu(position: UserMenuPosition::Sidebar)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn (): string => Blade::render("
                    <div>
                        <div
                            x-data
                            :class=\"\$store.sidebar.isOpen ? 'px-6' : 'px-2 justify-center'\"
                            class='flex items-center gap-x-3 py-4 transition-all duration-300'>
                            <img src='{{ asset('storage/side-bar_logo/ddo-logo.png') }}' alt='Logo' class='h-10 w-auto flex-shrink-0'>
                            <span
                                x-show='\$store.sidebar.isOpen'
                                x-transition:enter='transition ease-out duration-300'
                                x-transition:enter-start='opacity-0'
                                x-transition:enter-end='opacity-100'
                                class='text-sm font-bold leading-tight tracking-tight text-gray-950 dark:text-white whitespace-nowrap'>
                                BNS & <br> Adopt-A-Child
                            </span>
                        </div>
                        <div class='px-4 pb-2'>
                            <hr class='border-gray-200 dark:border-white/10'>
                        </div>
                    </div>
                "),
            )
            ->userMenuItems([])
            ->sidebarCollapsibleOnDesktop()
            ->breadcrumbs(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->pages([
                ListFamilyProfiles::class,
                ChildVisitLog::class,
                ChildVisitDetail::class,
            ])
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
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
