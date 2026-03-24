<?php

namespace App\Filament\Resources\BaranggayNutritionScholars\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                Select::make('barangay_name')
                    ->label('Barangay')
                    ->required()
                    ->searchable()
                    ->options(function () {
                        $barangays = Cache::remember('davao_de_oro_barangays', now()->addHours(24), function () {
                            $response = Http::get('https://psgc.cloud/api/provinces/1108200000/barangays');
                            return $response->successful() ? $response->json() : [];
                        });

                        return collect($barangays)
                            ->pluck('name', 'name')
                            ->toArray();
                    })
                    ->getSearchResultsUsing(function (string $search) {
                        $barangays = Cache::remember('davao_de_oro_barangays', now()->addHours(24), function () {
                            $response = Http::get('https://psgc.cloud/api/provinces/1108200000/barangays');
                            return $response->successful() ? $response->json() : [];
                        });

                        return collect($barangays)
                            ->filter(fn($b) => str_contains(strtolower($b['name']), strtolower($search)))
                            ->pluck('name', 'name')
                            ->toArray();
                    })
            ]);
    }
}
