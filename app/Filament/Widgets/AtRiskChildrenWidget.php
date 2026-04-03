<?php

namespace App\Filament\Widgets;

use App\Models\OfficeChildVisit;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class AtRiskChildrenWidget extends Widget
{
    use InteractsWithPageFilters;
    protected static ?int $sort = 3;
    protected string $view = 'filament.widgets.at-risk-children-widget';
    protected int|string|array $columnSpan = 2;

    public function getAtRiskProperty()
    {
        $year = (int) ($this->filters['year'] ?? now()->year);

        return OfficeChildVisit::whereIn(
            'id',
            OfficeChildVisit::whereYear('visit_date', $year)
                ->selectRaw('MAX(id)')
                ->groupBy('adopted_id')
        )
            ->with(['child', 'bns'])
            ->where(function ($q) {
                $q->where('status', 'like', '%Severely%')
                    ->orWhere('status', 'like', '%Wasted%')
                    ->orWhere('status', 'like', '%Obese%')
                    ->orWhere('status', 'like', '%Stunted%');
            })
            ->latest('visit_date')
            ->take(3)
            ->get();
    }
    protected function getExtraAttributes(): array
    {
        return [
            'class' => 'h-full',
        ];
    }
    public function getSelectedYearProperty(): int
    {
        return (int) ($this->filters['year'] ?? now()->year);
    }
}
