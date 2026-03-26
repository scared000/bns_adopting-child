<?php

namespace App\Filament\Widgets;

use App\Models\OfficeChildVisit;
use Filament\Widgets\Widget;

class RecentVisitsWidget extends Widget
{
    protected static ?int $sort = 3;
    protected string $view = 'filament.widgets.recent-visits-widget';
    protected int|string|array $columnSpan = 1;

    public function getRecentVisitsProperty()
    {
        return OfficeChildVisit::with(['child', 'bns'])
            ->latest('visit_date')
            ->take(5)
            ->get();
    }
}
