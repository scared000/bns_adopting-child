<?php

namespace App\Filament\Widgets;

use App\Models\OfficeChildVisit;
use Filament\Widgets\Widget;

class RecentVisitsWidget extends Widget
{
    protected static ?int $sort = 4;
    protected string $view = 'filament.widgets.recent-visits-widget';
    protected int|string|array $columnSpan = 'full';

    public function getRecentVisitsProperty()
    {
        return OfficeChildVisit::with(['child', 'bns', 'office', 'visitItems'])
            ->latest('visit_date')
            ->take(8)
            ->get();
    }
}
