<?php

namespace App\Filament\Resources\OfficeChildAssigns;

use AlizHarb\ActivityLog\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\OfficeChildAssigns\Pages\CreateOfficeChildAssign;
use App\Filament\Resources\OfficeChildAssigns\Pages\ListOfficeChildAssigns;
use App\Filament\Resources\OfficeChildAssigns\Schemas\OfficeChildAssignForm;
use App\Filament\Resources\OfficeChildAssigns\Tables\OfficeChildAssignsTable;
use App\Models\AdoptedChild;
use App\Models\BaranggayNutritionScholars;
use App\Models\Office;
use App\Models\OfficeChildAssign;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Collection;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use function Laravel\Prompts\search;

class OfficeChildAssignResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = OfficeChildAssign::class;
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Child Assignments';
//    protected static string|null|\UnitEnum $navigationGroup = 'MONITORING';
    protected static ?int $navigationSort = 1;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        if ($user?->hasRole('officeDistributor') && $user->office_id) {
            $query->where('office_id', $user->office_id);
        }

        return $query;
    }
    public static function form(Schema $schema): Schema
    {
        return OfficeChildAssignForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficeChildAssignsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListOfficeChildAssigns::route('/'),
            'create' => CreateOfficeChildAssign::route('/create'),
        ];
    }
}
