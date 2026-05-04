<?php

namespace App\Filament\Resources\Immunizations;

use AlizHarb\ActivityLog\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\AdoptedChildren\AdoptedChildResource;
use App\Filament\Resources\Immunizations\Pages\CreateImmunizations;
use App\Filament\Resources\Immunizations\Pages\EditImmunizations;
use App\Filament\Resources\Immunizations\Pages\ListImmunizations;
use App\Filament\Resources\Immunizations\Schemas\ImmunizationsForm;
use App\Filament\Resources\Immunizations\Tables\ImmunizationsTable;
use App\Models\AdoptedChild;
use App\Models\Immunizations;
use App\Models\OfficeChildAssign;
use BackedEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ImmunizationsResource extends Resource
{
    protected static ?string $model = Immunizations::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static string|null|\UnitEnum $navigationGroup = 'MONITORING';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Immunization Records';

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['child.municipality.province']);
        $user  = auth()->user();

        if ($user?->hasRole('bns')) {
            $bnsId = $user->bnsRecord?->id;

            $assignedChildIds = OfficeChildAssign::where('bns_id', $bnsId)
                ->pluck('adopted_id');

            $query->whereIn('child_id', $assignedChildIds);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ImmunizationsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ImmunizationsTable::configure($table);
    }



    public static function getPages(): array
    {
        return [
            'index'  => ListImmunizations::route('/'),
            'create' => CreateImmunizations::route('/create'),
            'edit'   => EditImmunizations::route('/{record}/edit'),
        ];
    }
}
