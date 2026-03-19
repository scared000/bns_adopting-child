<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', \App\Models\User::count())
                ->description('All registered users')
                ->color('success'),

            Stat::make('Revenue', '$12,345')
                ->description('This month')
                ->color('primary'),
        ];
    }
}
