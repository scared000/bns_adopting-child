<?php

namespace App\Filament\Resources\OfficeDistributions;

use App\Filament\Resources\OfficeDistributions\Pages\CreateOfficeDistribution;
use App\Filament\Resources\OfficeDistributions\Pages\ListOfficeDistributions;
use App\Filament\Resources\OfficeDistributions\Schemas\OfficeDistributionForm;
use App\Filament\Resources\OfficeDistributions\Tables\OfficeDistributionsTable;
use App\Models\OfficeChildVisit;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OfficeDistributionResource extends Resource
{
    protected static ?string $model = OfficeChildVisit::class;
    protected static ?string $modelLabel = 'Distribution';
    protected static ?string $pluralModelLabel = 'Item Distributions';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-gift';
    protected static string|null|\UnitEnum $navigationGroup = 'OFFICE ITEM DISTRIBUTIONS';

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    public static function form(Schema $schema): Schema
    {
        return OfficeDistributionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficeDistributionsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where('visit_type', 'office_distribution')
            ->withCount('visitItems')
            ->withSum('visitItems', 'item_amount');

        $user = auth()->user();

        if ($user?->hasRole('officeDistributor')) {
            $officeId = $user->office_id;

            $query->where('office_id', $officeId);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOfficeDistributions::route('/'),
        ];
    }
}
