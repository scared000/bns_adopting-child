<?php

namespace App\Filament\Resources\BaranggayNutritionScholars\Schemas;

use App\Models\Barangay;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class BaranggayNutritionScholarsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('profile_path')
                    ->disk('public')
                    ->directory('bns_profile')
                    ->visibility('public'),

                TextInput::make('firstname')
                    ->label('First Name')
                    ->required(),

                TextInput::make('middlename')
                    ->label('Middle Name'),
                TextInput::make('lastname')
                    ->label('Last Name')
                    ->required(),

                TextInput::make('suffix')
                    ->label('Suffix')
                    ->suffix('Jr./Sr.'),

                TextInput::make('purok')
                    ->label('Purok'),

                Select::make('municipality_id')
                    ->label('Municipality')
                    ->relationship(
                        name: 'municipality',
                        titleAttribute: 'citymunDesc'
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->citymunDesc} ({$record->province->provDesc})")
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(),

                Select::make('barangay_id')
                    ->label('Barangay')
                    ->options(function (Get $get) {
                        $municipalityCode = $get('municipality_id');
                        if (!$municipalityCode) {
                            return [];
                        }
                        return Barangay::where('citymunCode', $municipalityCode)
                            ->pluck('brgyDesc', 'brgyCode');
                    })
                    ->searchable()
                    ->required(),
            ]);
    }
}
