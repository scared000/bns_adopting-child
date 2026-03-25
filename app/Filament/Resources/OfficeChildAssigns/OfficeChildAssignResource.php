<?php

namespace App\Filament\Resources\OfficeChildAssigns;

use App\Filament\Resources\OfficeChildAssigns\Pages\CreateOfficeChildAssign;
use App\Filament\Resources\OfficeChildAssigns\Pages\ListOfficeChildAssigns;
use App\Models\AdoptedChild;
use App\Models\BaranggayNutritionScholars;
use App\Models\Office;
use App\Models\OfficeChildAssign;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Collection;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use function Laravel\Prompts\search;

class OfficeChildAssignResource extends Resource
{
    protected static ?string $model = OfficeChildAssign::class;
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Child Assignments';
    protected static string|null|\UnitEnum $navigationGroup = 'MONITORING';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bns_id')
                    ->label('Barangay Nutrition Scholar (BNS)')
                    ->options(
                        BaranggayNutritionScholars::all()
                            ->mapWithKeys(fn ($bns) => [
                                $bns->id => $bns->firstname . ' ' . $bns->lastname . ' — ' . ($bns->barangay?->brgyDesc ?? 'No Barangay')
                            ])
                    )
                    ->searchable()
                    ->required(),

                Select::make('adopted_id')
                    ->label('Child')
                    ->options(function ($record) {
                        return AdoptedChild::query()
                            ->where(function ($query) use ($record) {
                                $query->whereDoesntHave('officeAssignments');
                                if ($record?->adopted_id) {
                                    $query->orWhere('id', $record->adopted_id);
                                }
                            })
                            ->get()
                            ->mapWithKeys(fn ($child) => [
                                $child->id => $child->firstname . ' ' . $child->lastname
                            ]);
                    })
                    ->searchable()
                    ->required()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                Select::make('office_id')
                    ->label('Assigned Office')
                    ->options(
                        Office::all()->mapWithKeys(fn ($office) => [
                            $office->id => $office->office . ' (' . $office->short_name . ')'
                        ])
                    )
                    ->searchable()
                    ->required(),

                DatePicker::make('assigned_date')
                    ->label('Assigned Date')
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('child.firstname')
                    ->label('CHILD NAME')
                    ->formatStateUsing(fn ($record) => $record->child->firstname . ' ' . $record->child->lastname)
                    ->searchable(),

                TextColumn::make('barangay')
                    ->label('BARANGAY')
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        $bns = $record->bns;
                        return collect([
                            $bns?->purok,
                            $bns?->barangay?->brgyDesc,
                            $bns?->municipality?->citymunDesc,
                            $bns?->province?->provDesc,
                        ])->filter()->implode(', ') ?: '—';
                    }),

                TextColumn::make('office.office')
                    ->label('ASSIGNED OFFICE')
                    ->wrap()
                    ->badge()
                    ->color('gray')
                    ->getStateUsing(function ($record) {
                        $office = $record->office;
                        return collect([
                            $office->office,
                            $office->short_name ? "({$office->short_name})" : null,
                        ])->filter()->implode(' ') ?: '—';
                    })
                    ->searchable(),

                TextColumn::make('bns.firstname')
                    ->label('ASSIGNED BNS')
                    ->formatStateUsing(fn ($record) => $record->bns->firstname . ' ' . $record->bns->lastname)
                    ->searchable(),

                TextColumn::make('assigned_date')
                    ->label('ASSIGNED DATE')
                    ->date()
                    ->placeholder('—'),

                TextColumn::make('visit_done')
                    ->label('VISIT DONE')
                    ->default('None'),

            ])
            ->recordActions([
                EditAction::make('edit')->icon('heroicon-o-pencil'),
                Action::make('unassign')
                    ->label('Unassign')
                    ->icon('heroicon-m-user-minus')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Unassign Child')
                    ->modalDescription('Are you sure you want to remove this child from the BNS? This will free up the child for a new assignment.')
                    ->action(function (OfficeChildAssign $record) {
                        $record->delete();
                        Notification::make()
                            ->title('Child unassigned successfully')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->recordActionsColumnLabel('ACTION')
            ->toolbarActions([
//                BulkAction::make('bulk_unassign')
//                    ->label('Unassign Selected')
//                    ->icon('heroicon-o-x-circle')
//                    ->color('danger')
//                    ->requiresConfirmation()
//                    ->action(fn (Collection $records) => $records->each->delete())
//                    ->after(fn () => Notification::make()->title('Selected children unassigned')->success()->send()),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListOfficeChildAssigns::route('/'),
            'create' => CreateOfficeChildAssign::route('/create'),
        ];
    }
}
