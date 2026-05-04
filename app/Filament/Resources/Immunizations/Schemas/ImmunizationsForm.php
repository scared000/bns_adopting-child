<?php

namespace App\Filament\Resources\Immunizations\Schemas;

use App\Models\AdoptedChild;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ImmunizationsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('child_id')
                    ->label('Child')
                    ->options(
                        AdoptedChild::all()->mapWithKeys(fn ($c) => [
                            $c->id => $c->firstname . ' ' . $c->lastname
                        ])
                    )
                    ->searchable()
                    ->required(),

                Select::make('vaccine_description')
                    ->label('Vaccine / Description')
                    ->options([
                        'BCG' => 'BCG',
                        'Hepatitis B' => 'Hepatitis B',
                        'Pentavalent' => 'Pentavalent (DPT-HepB-Hib)',
                        'OPV' => 'OPV (Oral Polio)',
                        'IPV' => 'IPV (Inactivated Polio)',
                        'PCV' => 'PCV (Pneumococcal)',
                        'MMR' => 'MMR (Measles, Mumps, Rubella)',
                        'MCV' => 'MCV (Measles-Containing)',
                        'Vitamin A' => 'Vitamin A Supplementation',
                        'Rotavirus' => 'Rotavirus',
                        'Influenza' => 'Influenza',
                        'Other' => 'Other',
                    ])
                    ->searchable()
                    ->required(),

                DatePicker::make('dose_1')->label('1st Dose Date')->native(false),
                DatePicker::make('dose_2')->label('2nd Dose Date')->native(false),
                DatePicker::make('dose_3')->label('3rd Dose Date')->native(false),

                Textarea::make('remarks')
                    ->label('Remarks')
                    ->rows(2)
                    ->columnSpanFull(),
            ])->columns(2);
    }
}
