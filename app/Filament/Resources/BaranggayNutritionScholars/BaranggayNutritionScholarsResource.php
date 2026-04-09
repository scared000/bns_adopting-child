<?php

namespace App\Filament\Resources\BaranggayNutritionScholars;

use AlizHarb\ActivityLog\RelationManagers\ActivitiesRelationManager;
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
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;

class BaranggayNutritionScholarsResource extends Resource
{
    protected static ?string $model = BaranggayNutritionScholars::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-heart';
    protected static string|null|\UnitEnum $navigationGroup = 'MONITORING';
    public static ?int $navigationSort = 3;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereHas('user', function ($query){
            $query->role('bns');
        })->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

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
            ActivitiesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('user', function (Builder $query) {
                $query->role('bns');
            });
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
