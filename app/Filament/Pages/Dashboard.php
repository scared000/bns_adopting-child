<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AtRiskChildrenWidget;
use App\Filament\Widgets\NutritionStatusWidget;
use App\Filament\Widgets\RecentVisitsWidget;
use App\Filament\Widgets\StatsOverview;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $title = 'My Dashboard';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-squares-2x2';
    protected static string $routePath = '/';

    public function getColumns(): array|int
    {
        return [
            'default' => 1,
            'md'      => 2,
            'xl'      => 4,
        ];
    }

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            NutritionStatusWidget::class,
            AtRiskChildrenWidget::class,
            RecentVisitsWidget::class,
        ];
    }
}
