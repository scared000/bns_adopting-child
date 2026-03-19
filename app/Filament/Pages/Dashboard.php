<?php

namespace App\Filament\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $title = 'My Dashboard';
    protected static string|null|\UnitEnum $navigationGroup = 'OVERVIEW';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string $routePath = '/';

    public function getColumns(): array|int
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    // Optional: manually define which widgets appear (disables auto-discovery)
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
        ];
    }
}
