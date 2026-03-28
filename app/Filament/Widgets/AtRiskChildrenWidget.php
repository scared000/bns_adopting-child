<?php

namespace App\Filament\Widgets;

use App\Models\OfficeChildVisit;
use Filament\Widgets\Widget;

class AtRiskChildrenWidget extends Widget
{
    protected static ?int $sort = 3;
    protected string $view = 'filament.widgets.at-risk-children-widget';
    protected int|string|array $columnSpan = 2;

    public function getAtRiskProperty()
    {
        return OfficeChildVisit::whereIn(
            'id',
            OfficeChildVisit::selectRaw('MAX(id)')
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
            ->take(6)
            ->get();
    }
}
