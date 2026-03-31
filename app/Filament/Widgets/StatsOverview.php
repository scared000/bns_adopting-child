<?php

namespace App\Filament\Widgets;

use App\Models\AdoptedChild;
use App\Models\BaranggayNutritionScholars;
use App\Models\OfficeChildVisit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalChildren = AdoptedChild::count();
        $totalBns      = BaranggayNutritionScholars::count();
        $totalVisits   = OfficeChildVisit::count();

        $latestVisitIds = OfficeChildVisit::selectRaw('MAX(id) as id')
            ->groupBy('adopted_id')
            ->pluck('id');

        $latestStatuses = OfficeChildVisit::whereIn('id', $latestVisitIds)
            ->pluck('status');

        $normalCount = $latestStatuses->filter(
            fn ($s) => str_contains(strtolower($s ?? ''), 'normal')
        )->count();

        $atRiskCount = $latestStatuses->filter(function ($s) {
            $s = strtolower($s ?? '');
            return str_contains($s, 'severely') ||
                    str_contains($s, 'wasted') ||
                    str_contains($s, 'obese') ||
                    str_contains($s, 'stunted');
        })->count();

        $normalPercent = $totalChildren > 0
            ? round(($normalCount / $totalChildren) * 100, 1)
            : 0;

        $thisMonthVisits = OfficeChildVisit::whereMonth('visit_date', now()->month)
            ->whereYear('visit_date', now()->year)
            ->count();

        return [
            Stat::make('Total Children', number_format($totalChildren))
                ->description('Registered in the system')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info')
                ->icon('heroicon-o-user-group'),

            Stat::make('Total BNS', number_format($totalBns))
                ->description('Barangay Nutrition Scholars')
                ->descriptionIcon('heroicon-m-heart')
                ->color('warning')
                ->icon('heroicon-o-heart'),

            Stat::make('Normal Status', number_format($normalCount))
                ->description("{$normalPercent}% of total children")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('At-Risk Children', number_format($atRiskCount))
                ->description('Severely UW, Wasted, Obese or Stunned')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('Total Visits', number_format($totalVisits))
                ->description('All recorded visits')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('gray')
                ->icon('heroicon-o-clipboard-document-check'),

            Stat::make('Visits This Month', number_format($thisMonthVisits))
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary')
                ->icon('heroicon-o-calendar-days'),
        ];
    }
}
