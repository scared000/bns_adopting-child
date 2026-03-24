<?php

namespace App\Filament\Resources\OfficeChildAssigns\Pages;

use App\Filament\Resources\OfficeChildAssigns\OfficeChildAssignResource;
use App\Models\BaranggayNutritionScholars;
use App\Models\OfficeChildAssign;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ListOfficeChildAssigns extends ListRecords
{
    protected static string $resource = OfficeChildAssignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Assign Child')
                ->url(route('filament.admin.resources.office-child-assigns.create')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->bulkActions([
                BulkAction::make('bulk_assign')
                    ->label('Assign to BNS')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Select::make('bns_id')
                            ->label('Select BNS')
                            ->options(
                                BaranggayNutritionScholars::all()
                                    ->mapWithKeys(fn ($bns) => [
                                        $bns->id => $bns->firstname . ' ' . $bns->lastname . ' — ' . ($bns->barangay?->brgyDesc ?? 'No Barangay')
                                    ])
                            )
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $records->each(function ($record) use ($data) {
                            $record->update(['bns_id' => $data['bns_id']]);
                        });
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}
