<?php

namespace App\Filament\Resources\AdoptedChildren;

use AlizHarb\ActivityLog\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\AdoptedChildren\Pages\CreateAdoptedChild;
use App\Filament\Resources\AdoptedChildren\Pages\EditAdoptedChild;
use App\Filament\Resources\AdoptedChildren\Pages\ListAdoptedChildren;
use App\Filament\Resources\AdoptedChildren\Pages\ViewAdoptedChild;
use App\Filament\Resources\AdoptedChildren\RelationManagers\ImmunizationsChildRelationManager;
use App\Filament\Resources\AdoptedChildren\Schemas\AdoptedChildForm;
use App\Filament\Resources\AdoptedChildren\Tables\AdoptedChildrenTable;
use App\Models\AdoptedChild;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdoptedChildResource extends Resource
{
    protected static ?string $model = AdoptedChild::class;
    protected static ?string $modelLabel = 'Child';
    protected static ?string $pluralModelLabel = 'Children';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-face-smile';
    protected static string|null|\UnitEnum $navigationGroup = 'Monitoring';

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return AdoptedChildForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdoptedChildrenTable::configure($table);
    }
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();

        if ($user?->hasRole('bns')) {
            $bnsId = $user->bnsRecord?->id;

            $query->whereHas('officeAssignments', function (Builder $q) use ($bnsId) {
                $q->where('bns_id', $bnsId);
            });
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
//            ActivitiesRelationManager::class,
//            ImmunizationsChildRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'view' => ViewAdoptedChild::route('{record}'),
            'index' => ListAdoptedChildren::route('/'),
//            'edit' => EditAdoptedChild::route('/{record}/edit'),
        ];
    }
}
