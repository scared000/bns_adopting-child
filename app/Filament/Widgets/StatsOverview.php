<?php

namespace App\Filament\Widgets;

use App\Models\AdoptedChild;
use App\Models\BaranggayNutritionScholars;
use App\Models\OfficeChildVisit;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $year = (int) ($this->filters['year'] ?? now()->year);
        $municipalityId = $this->filters['municipality_id'] ?? null;

        $activeChildrenCount = AdoptedChild::query()
            ->when($municipalityId, fn ($q) => $q->where('municipality_id', $municipalityId))
            ->whereHas('officeVisits', fn ($q) => $q->whereYear('visit_date', $year))
            ->count();

        $totalBns = BaranggayNutritionScholars::count();

        $totalVisits = OfficeChildVisit::whereYear('visit_date', $year)
            ->when($municipalityId, fn ($q) => $q->whereHas(
                'child', fn ($q) => $q->where('municipality_id', $municipalityId)
            ))
            ->count();

        $latestVisitIds = OfficeChildVisit::whereYear('visit_date', $year)
            ->when($municipalityId, fn ($q) => $q->whereHas(
                'child', fn ($q) => $q->where('municipality_id', $municipalityId)
            ))
            ->selectRaw('MAX(id) as id')
            ->groupBy('adopted_id')
            ->pluck('id');

        $latestStatuses = OfficeChildVisit::whereIn('id', $latestVisitIds)->pluck('status');

        $normalCount = $latestStatuses->filter(fn ($s) => str_contains(strtolower($s ?? ''), 'normal'))->count();
        $atRiskCount = $latestStatuses->filter(function ($s) {
            $s = strtolower($s ?? '');
            return str_contains($s, 'severely') ||
                str_contains($s, 'wasted') ||
                str_contains($s, 'obese') ||
                str_contains($s, 'stunted');
        })->count();

        $normalPercent = $activeChildrenCount > 0
            ? round(($normalCount / $activeChildrenCount) * 100, 1)
            : 0;

        $thisMonthVisits = OfficeChildVisit::whereYear('visit_date', $year)
            ->whereMonth('visit_date', now()->month)
            ->when($municipalityId, fn ($q) => $q->whereHas(
                'child', fn ($q) => $q->where('municipality_id', $municipalityId)
            ))
            ->count();

        $yearLabel = (string) $year;

        return [
            Stat::make('Total Children', number_format($activeChildrenCount))
                ->description("Monitored in {$yearLabel}")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->icon('heroicon-o-user-group'),

            Stat::make('Total BNS', number_format($totalBns))
                ->description('Barangay Nutrition Scholars')
                ->descriptionIcon('heroicon-m-heart')
                ->color('warning')
                ->icon('heroicon-o-heart'),

            Stat::make('Normal Status', number_format($normalCount))
                ->description("{$normalPercent}% of children monitored in {$yearLabel}")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('At-Risk Children', number_format($atRiskCount))
                ->description("Found in {$yearLabel} visits")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('Total Visits', number_format($totalVisits))
                ->description("All records for {$yearLabel}")
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('gray')
                ->icon('heroicon-o-clipboard-document-check'),

            Stat::make('Visits This Month', number_format($thisMonthVisits))
                ->description(now()->format('F') . " {$yearLabel}")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary')
                ->icon('heroicon-o-calendar-days'),
        ];
    }
}
