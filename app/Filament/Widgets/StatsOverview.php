<?php

namespace App\Filament\Widgets;

use App\Models\AdoptedChild;
use App\Models\BaranggayNutritionScholars;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        $totalChildren = AdoptedChild::count();
        $totalBns      = BaranggayNutritionScholars::count();
        $normalCount   = AdoptedChild::where('nutritional_status', 'like', '%Normal%')
            ->orWhere('nutritional_status', 'like', '%normal%')
            ->count();
        $normalPercent = $totalChildren > 0
            ? round(($normalCount / $totalChildren) * 100, 1)
            : 0;

        return [
            Stat::make('Total Children', $totalChildren)
                ->description('Registered in the system')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->icon('heroicon-o-user-group'),

            Stat::make('Total BNS', $totalBns)
                ->description('Barangay Nutrition Scholars')
                ->descriptionIcon('heroicon-m-heart')
                ->color('warning')
                ->icon('heroicon-o-heart'),

            Stat::make('Normal Status', $normalCount)
                ->description("{$normalPercent}% of total children")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
