<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $title = 'My Dashboard';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string $routePath = '/';

    public function getColumns(): array|int
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }
}
