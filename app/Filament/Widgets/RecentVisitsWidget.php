<?php

namespace App\Filament\Widgets;

use App\Models\OfficeChildVisit;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class RecentVisitsWidget extends Widget
{
    use InteractsWithPageFilters;
    protected static ?int $sort = 4;
    protected string $view = 'filament.widgets.recent-visits-widget';
    protected int|string|array $columnSpan = 'full';

    public function getRecentVisitsProperty()
    {
        $year = (int) ($this->filters['year'] ?? now()->year);
        $municipalityId = $this->filters['municipality_id'] ?? null;

        return OfficeChildVisit::with(['child', 'bns', 'office', 'visitItems'])
            ->whereYear('visit_date', $year)
            ->when($municipalityId, fn ($q) => $q->whereHas(
                'child', fn ($q) => $q->where('municipality_id', $municipalityId)
            ))
            ->latest('visit_date')
            ->take(8)
            ->get();
    }

    public function getSelectedYearProperty(): int
    {
        return (int) ($this->filters['year'] ?? now()->year);
    }
}
