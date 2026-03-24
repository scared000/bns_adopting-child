<?php

namespace App\Filament\Widgets;

use App\Models\AdoptedChild;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('TOTAL CHILDREN', AdoptedChild::count())
                ->description('This Quarter')
                ->color('success'),

            Stat::make('NORMAL STATUS', '$12,345')
                ->description('This Quarter')
                ->color('primary'),

            Stat::make('UNDERWEIGHT', '$12,345')
                ->description('This Quarter')
                ->color('warning'),

            Stat::make('SEVERELY UNDRWT.', '$12,345')
                ->description('This Quarter')
                ->color('danger'),
        ];
    }
}
