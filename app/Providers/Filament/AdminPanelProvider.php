<?php

namespace App\Providers\Filament;

use AlizHarb\ActivityLog\ActivityLogPlugin;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\ChildVisitDetail;
use App\Filament\Pages\Dashboard;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use App\Filament\Pages\ChildVisitLog;
use Cmsmaxinc\FilamentErrorPages\FilamentErrorPagesPlugin;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Notifications\Notification;
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
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;

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
            ->databaseNotifications()
            ->databaseNotificationsPolling('20')
            ->topNavigation(false)
//            ->topbar(false)
            ->globalSearch(false)
//            ->userMenu(position: UserMenuPosition::Topbar)
            ->homeUrl(fn () => auth()->user()?->hasRole('super_admin')
                ? route('filament.admin.pages.dashboard')
                : route('filament.admin.auth.profile'))
            ->profile(page: EditProfile::class, isSimple: false)
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
                            :class=\"\$store.sidebar.isOpen ? 'px-6' : 'px-3 justify-center'\"
                            class='flex items-center gap-x-4 py-6 transition-all duration-300'>
                            <img src='{{ asset('storage/side-bar_logo/ddo-logo.png') }}' alt='Logo' class='h-16 w-auto flex-shrink-0'>
                            <span
                                x-show='\$store.sidebar.isOpen'
                                x-transition:enter='transition ease-out duration-300'
                                x-transition:enter-start='opacity-0'
                                x-transition:enter-end='opacity-100'
                                class='text-base font-bold leading-snug tracking-tight text-gray-950 dark:text-white whitespace-nowrap'>
                                BNS & <br> Adopt-A-Child
                            </span>
                        </div>
                        <div class='px-4 pb-3'>
                            <hr class='border-gray-200 dark:border-white/10'>
                        </div>
                    </div>
                "),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => '<div x-data x-on:open-print-tab.window="window.open($event.detail.url, \'_blank\')"></div>',
            )
            ->userMenuItems([])
            ->sidebarCollapsibleOnDesktop()
            ->breadcrumbs(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->pages([
                ChildVisitLog::class,
                ChildVisitDetail::class,
                Dashboard::class,
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
            ->plugins([
                // need to fix this route
                FilamentErrorPagesPlugin::make()
                    ->routes([
                        'admin/*',
                    ]),
                FilamentApexChartsPlugin::make(),
                ActivitylogPlugin::make()
                    ->label('Activity log')
                    ->navigationGroup('SYSTEM MANAGEMENT')
                    ->navigationSort(200)
                    ->navigationIcon('heroicon-o-clipboard-document-list'),
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ])
                    ->navigationGroup('SYSTEM MANAGEMENT')
                    ->navigationSort(200)
                    ->navigationIcon('heroicon-o-shield-check'),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
