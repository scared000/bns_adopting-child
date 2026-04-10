<?php

namespace App\Filament\Widgets;

use App\Models\AdoptedChild;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RehabilitationRateWidget extends ApexChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 1;
    protected static ?string $chartId = 'rehabilitationRate';
    protected static ?string $heading = 'Rehabilitation Rate';
    protected static ?string $subheading = 'Based on WFA & WFH status';

    public function updatedFilters(): void
    {
        $this->updateOptions();
    }

    private function isRehabilitated(string $status): bool
    {
        $wfaMatch = preg_match('/WFA:\s*([^|]+)/i', $status, $wfa);
        $wfhMatch = preg_match('/WFH:\s*([^|]+)/i', $status, $wfh);

        if (! $wfaMatch && ! $wfhMatch) {
            return strtolower(trim($status)) === 'normal';
        }

        $wfaNormal = $wfaMatch && strtolower(trim($wfa[1])) === 'normal';
        $wfhNormal = $wfhMatch && strtolower(trim($wfh[1])) === 'normal';

        return $wfaNormal && $wfhNormal;
    }

    protected function getOptions(): array
    {
        $year           = (int) ($this->filters['year'] ?? now()->year);
        $municipalityId = $this->filters['municipality_id'] ?? null;

        $children = AdoptedChild::query()
            ->when($municipalityId, fn ($q) => $q->where('municipality_id', $municipalityId))
            ->with([
                'officeVisits' => fn ($q) => $q
                    ->whereYear('visit_date', $year)
                    ->orderBy('visit_date', 'desc'),
            ])
            ->get();

        $rehabilitated    = 0;
        $notRehabilitated = 0;
        $notAssessed      = 0;

        foreach ($children as $child) {
            $latestVisit = $child->officeVisits->first();

            if (! $latestVisit || empty($latestVisit->status)) {
                $notAssessed++;
                continue;
            }

            $this->isRehabilitated($latestVisit->status)
                ? $rehabilitated++
                : $notRehabilitated++;
        }

        $series = [$rehabilitated, $notRehabilitated];
        $labels = [
            "Rehabilitated: {$rehabilitated}",
            "Not Yet Rehabilitated: {$notRehabilitated}",
        ];
        $colors = ['#22c55e', '#f97316'];

        if ($notAssessed > 0) {
            $series[] = $notAssessed;
            $labels[] = "Not Yet Assessed: {$notAssessed}";
            $colors[] = '#94a3b8';
        }

        return [
            'chart' => [
                'type'    => 'pie',
                'height'  => 320,
                'toolbar' => ['show' => false],
            ],
            'series' => $series,
            'labels' => $labels,
            'colors' => $colors,
            'legend' => [
                'position' => 'bottom',
                'fontSize' => '13px',
                'markers'  => [
                    'fillColors' => $colors,
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'style'   => ['fontSize' => '13px', 'fontWeight' => '700'],
                'formatter' => 'function(val, opts) {
                    var count = opts.w.config.series[opts.seriesIndex];
                    var pct   = Math.round(val * 10) / 10;
                    return count + " (" + pct + "%)";
                }',
                'dropShadow' => ['enabled' => false],
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function(val) { return val + " children"; }',
                ],
            ],
            'plotOptions' => [
                'pie' => [
                    'dataLabels' => ['offset' => -20],
                ],
            ],
        ];
    }
}
