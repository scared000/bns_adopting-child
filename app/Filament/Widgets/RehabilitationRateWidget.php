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
    protected static ?int $contentHeight = 172;

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
        $year = (int) ($this->filters['year'] ?? now()->year);
        $municipalityId = $this->filters['municipality_id'] ?? null;

        $children = AdoptedChild::query()
            ->when($municipalityId, fn ($q) => $q->where('municipality_id', $municipalityId))
            ->with([
                'officeVisits' => fn ($q) => $q
                    ->whereYear('visit_date', $year)
                    ->orderBy('visit_date', 'desc'),
            ])
            ->get();

        $rehabilitated = 0;
        $notRehabilitated = 0;
        $notAssessed = 0;

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
            "Rehabilitated: $rehabilitated ",
            "Not Yet Rehabilitated: $notRehabilitated",
        ];
        $colors = ['#22c55e', '#f97316'];

        if ($notAssessed > 0) {
            $series[] = $notAssessed;
            $labels[] = "Not Yet Assessed: $notAssessed";
            $colors[] = '#94a3b8';
        }

        return [
            'chart' => [
                'type'    => 'pie',
                'height'  => 172,
                'toolbar' => ['show' => false],
            ],
            'series' => $series,
            'labels' => $labels,
            'colors' => $colors,
            'legend' => [
                'position'  => 'right',
                'fontSize'  => '12px',
                'offsetY'   => 0,
                'markers'   => [
                    'fillColors' => $colors,
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'tooltip' => [
                'enabled' => false,
            ],
            'plotOptions' => [
                'pie' => [
                    'dataLabels' => ['offset' => -12],
                ],
            ],
            'stroke' => ['width' => 0],
        ];
    }
}
