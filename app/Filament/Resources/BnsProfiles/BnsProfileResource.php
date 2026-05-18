<?php

namespace App\Filament\Resources\BnsProfiles;

use App\Filament\Resources\BnsProfiles\Pages\CreateBnsProfile;
use App\Filament\Resources\BnsProfiles\Pages\EditBnsProfile;
use App\Filament\Resources\BnsProfiles\Pages\ListBnsProfiles;
use App\Filament\Resources\BnsProfiles\Pages\ViewBnsProfile;
use App\Filament\Resources\BnsProfiles\RelationManagers\TrainingsRelationManager;
use App\Filament\Resources\BnsProfiles\Schemas\BnsProfileForm;
use App\Filament\Resources\BnsProfiles\Tables\BnsProfilesTable;
use App\Models\BnsProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BnsProfileResource extends Resource
{
    protected static ?string $model = BnsProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $navigationLabel = 'BNS Profiles';
    protected static string|null|\UnitEnum $navigationGroup = 'BNS Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return BnsProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BnsProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TrainingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBnsProfiles::route('/'),
            'create' => CreateBnsProfile::route('/create'),
            'view'   => ViewBnsProfile::route('/{record}'),
            'edit'   => EditBnsProfile::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::active()->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }
}
