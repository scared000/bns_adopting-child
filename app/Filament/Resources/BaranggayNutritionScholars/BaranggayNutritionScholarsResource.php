<?php

namespace App\Filament\Resources\BaranggayNutritionScholars;

use App\Filament\Resources\BaranggayNutritionScholars\Pages\CreateBaranggayNutritionScholars;
use App\Filament\Resources\BaranggayNutritionScholars\Pages\EditBaranggayNutritionScholars;
use App\Filament\Resources\BaranggayNutritionScholars\Pages\ListBaranggayNutritionScholars;
use App\Filament\Resources\BaranggayNutritionScholars\Pages\ViewBaranggayNutritionScholars;
use App\Filament\Resources\BaranggayNutritionScholars\Schemas\BaranggayNutritionScholarsForm;
use App\Filament\Resources\BaranggayNutritionScholars\Tables\BaranggayNutritionScholarsTable;
use App\Models\BaranggayNutritionScholars;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BaranggayNutritionScholarsResource extends Resource
{
    protected static ?string $model = BaranggayNutritionScholars::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-heart';
    protected static string|null|\UnitEnum $navigationGroup = 'OVERVIEW';
    public static ?int $navigationSort = 3;


    public static function form(Schema $schema): Schema
    {
        return BaranggayNutritionScholarsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BaranggayNutritionScholarsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBaranggayNutritionScholars::route('/'),
            'create' => CreateBaranggayNutritionScholars::route('/create'),
            'edit' => EditBaranggayNutritionScholars::route('/{record}/edit'),
            'view'   => ViewBaranggayNutritionScholars::route('/{record}'),
        ];
    }
}
