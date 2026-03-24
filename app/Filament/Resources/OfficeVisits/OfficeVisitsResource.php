<?php

namespace App\Filament\Resources\OfficeVisits;

use App\Filament\Resources\OfficeVisits\Pages\CreateOfficeVisits;
use App\Filament\Resources\OfficeVisits\Pages\EditOfficeVisits;
use App\Filament\Resources\OfficeVisits\Pages\ListOfficeVisits;
use App\Filament\Resources\OfficeVisits\Schemas\OfficeVisitsForm;
use App\Filament\Resources\OfficeVisits\Tables\OfficeVisitsTable;
use App\Models\OfficeChildVisit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OfficeVisitsResource extends Resource
{
    protected static ?string $model = OfficeChildVisit::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static string|null|\UnitEnum $navigationGroup = 'MONITORING';
    protected static ?int $navigationSort = 2;


    public static function form(Schema $schema): Schema
    {
        return OfficeVisitsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficeVisitsTable::configure($table);
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
            'index' => ListOfficeVisits::route('/'),
            'create' => CreateOfficeVisits::route('/create'),
            'edit' => EditOfficeVisits::route('/{record}/edit'),
        ];
    }
}
