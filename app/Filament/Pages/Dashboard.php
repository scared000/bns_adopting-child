<?php

namespace App\Filament\Pages;

use App\Exports\MonthlyMonitoringExport;
use App\Filament\Widgets\AtRiskChildrenWidget;
use App\Filament\Widgets\NutritionStatusWidget;
use App\Filament\Widgets\RecentVisitsWidget;
use App\Filament\Widgets\StatsOverview;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Maatwebsite\Excel\Facades\Excel;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;
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

    public function filtersForm(Schema $schema): Schema
    {
        $years = collect(range(now()->year - 4, now()->year))
            ->mapWithKeys(fn ($y) => [$y => (string) $y])
            ->all();

        return $schema->schema([
            Select::make('year')
                ->label('Filter by Year')
                ->options($years)
                ->default(now()->year)
                ->native(false)
                ->placeholder('All years')
                ->prefixIcon('heroicon-o-calendar-days'),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Report')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->modalWidth('5xl')
                ->form([
                    Section::make('Report Period')
                        ->columns(2)
                        ->schema([
                            Select::make('month')
                                ->label('Month')
                                ->options(collect(range(1, 12))->mapWithKeys(
                                    fn ($m) => [$m => \Carbon\Carbon::createFromDate(null, $m)->format('F')]
                                ))
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
                        ]),

                    Section::make('Signatories')
                        ->description('Names and designations that will appear on the printed report.')
                        ->columns(2)
                        ->schema([
                            TextInput::make('prepared_by_name')
                                ->label('Prepared By — Name')
                                ->placeholder('e.g. MARIANITA M. CARIÑO')
                                ->default('MARIANITA M. CARIÑO')
                                ->required(),

                            TextInput::make('prepared_by_title')
                                ->label('Prepared By — Title / Designation')
                                ->placeholder('e.g. ND-III/PNC')
                                ->default('ND-III/PNC')
                                ->required(),

                            TextInput::make('noted_by_name')
                                ->label('Noted By — Name')
                                ->placeholder('e.g. ANALYN S. VIGILLA, MRDM')
                                ->default('ANALYN S. VIGILLA, MRDM')
                                ->required(),

                            TextInput::make('noted_by_title')
                                ->label('Noted By — Title / Designation')
                                ->placeholder('e.g. PPO IV/PNC')
                                ->default('PPO IV/PNC')
                                ->required(),

                            TextInput::make('approved_by_name')
                                ->label('Approved By — Name')
                                ->placeholder('e.g. ANTONIO P. YBIERNAS JR., MD, MPM')
                                ->default('ANTONIO P. YBIERNAS JR., MD, MPM')
                                ->required(),

                            TextInput::make('approved_by_title')
                                ->label('Approved By — Title / Designation')
                                ->placeholder('e.g. PG-Department Head')
                                ->default('PG-Department Head')
                                ->required(),
                        ]),
                ])
                ->action(function (array $data) {
                    return Excel::download(
                        new MonthlyMonitoringExport(
                            month: $data['month'],
                            year:  $data['year'],
                            signatories: [
                                'prepared_by_name' => strtoupper($data['prepared_by_name']),
                                'prepared_by_title' => $data['prepared_by_title'],
                                'noted_by_name' => strtoupper($data['noted_by_name']),
                                'noted_by_title' => $data['noted_by_title'],
                                'approved_by_name'   => strtoupper($data['approved_by_name']),
                                'approved_by_title' => $data['approved_by_title'],
                            ],
                        ),
                        'monthly-monitoring-'
                        . $data['year'] . '-'
                        . str_pad($data['month'], 2, '0', STR_PAD_LEFT)
                        . '.xlsx'
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
