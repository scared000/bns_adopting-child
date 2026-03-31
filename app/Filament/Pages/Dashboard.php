<?php

namespace App\Filament\Pages;

use App\Exports\MonthlyMonitoringExport;
use App\Filament\Widgets\AtRiskChildrenWidget;
use App\Filament\Widgets\NutritionStatusWidget;
use App\Filament\Widgets\RecentVisitsWidget;
use App\Filament\Widgets\StatsOverview;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Maatwebsite\Excel\Facades\Excel;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $title = 'DASHBOARD';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-home';
    protected static string $routePath = '/';

    public int $exportMonth;
    public int $exportYear;

    public function mount(): void
    {
        $this->exportMonth = now()->month;
        $this->exportYear  = now()->year;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Report')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->form([
                    Select::make('month')
                        ->label('Month')
                        ->options(collect(range(1, 12))->mapWithKeys(fn ($m) => [
                            $m => \Carbon\Carbon::createFromDate(null, $m)->format('F')
                        ]))
                        ->default(now()->month)
                        ->required()
                        ->native(false),

                    Select::make('year')
                        ->label('Year')
                        ->options(collect(range(now()->year - 2, now()->year + 1))
                            ->mapWithKeys(fn ($y) => [$y => $y]))
                        ->default(now()->year)
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data) {
                    return Excel::download(
                        new MonthlyMonitoringExport($data['month'], $data['year']),
                        'monthly-monitoring-' . $data['year'] . '-' . str_pad($data['month'], 2, '0', STR_PAD_LEFT) . '.xlsx'
                    );
                }),
        ];
    }

    public function getColumns(): array|int
    {
        return [
            'default' => 1,
            'md'      => 2,
            'xl'      => 4,
        ];
    }

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            NutritionStatusWidget::class,
            AtRiskChildrenWidget::class,
            RecentVisitsWidget::class,
        ];
    }
}
