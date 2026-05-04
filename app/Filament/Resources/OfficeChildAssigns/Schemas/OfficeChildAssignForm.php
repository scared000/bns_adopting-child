<?php

namespace App\Filament\Resources\OfficeChildAssigns\Schemas;

use App\Models\AdoptedChild;
use App\Models\BaranggayNutritionScholars;
use App\Models\Office;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class OfficeChildAssignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bns_id')
                    ->label('Barangay Nutrition Scholar (BNS)')
                    ->options(
                        BaranggayNutritionScholars::whereHas('user', function ($query){
                            $query->role('bns');
                        })
                            ->get()
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
                    ->dehydrated(true)
                    ->multiple()
                    ->searchable()
                    ->required()
                    ->helperText('You can select multiple children at once.'),

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
}
