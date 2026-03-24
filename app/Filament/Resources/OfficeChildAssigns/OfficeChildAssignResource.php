<?php

namespace App\Filament\Resources\OfficeChildAssigns;

use App\Filament\Resources\OfficeChildAssigns\Pages\CreateOfficeChildAssign;
use App\Filament\Resources\OfficeChildAssigns\Pages\ListOfficeChildAssigns;
use App\Models\AdoptedChild;
use App\Models\BaranggayNutritionScholars;
use App\Models\OfficeChildAssign;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OfficeChildAssignResource extends Resource
{
    protected static ?string $model = OfficeChildAssign::class;
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Child Assignments';
    protected static string|null|\UnitEnum $navigationGroup = 'CHILD ASSIGNMENT';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bns_id')
                    ->label('Barangay Nutrition Scholar (BNS)')
                    ->options(
                        BaranggayNutritionScholars::all()
                            ->mapWithKeys(fn ($bns) => [
                                $bns->id => $bns->firstname . ' ' . $bns->lastname . ' — ' . $bns->barangay_name
                            ])
                    )
                    ->searchable()
                    ->required(),

                Select::make('adopted_id')
                    ->label('Child')
                    ->options(
                        AdoptedChild::all()
                            ->mapWithKeys(fn ($child) => [
                                $child->id => $child->firstname . ' ' . $child->lastname
                            ])
                    )
                    ->searchable()
                    ->required(),

                DatePicker::make('assigned_date')  // ✅ DatePicker not TextColumn
                ->label('Assigned Date')
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bns.firstname')
                    ->label('BNS NAME')
                    ->formatStateUsing(fn ($record) => $record->bns->firstname . ' ' . $record->bns->lastname)
                    ->searchable(),
                TextColumn::make('child.firstname')
                    ->label('CHILD NAME')
                    ->formatStateUsing(fn ($record) => $record->child->firstname . ' ' . $record->child->lastname)
                    ->searchable(),
                TextColumn::make('bns.barangay_name')
                    ->label('BARANGAY'),
                TextColumn::make('assigned_date')
                    ->label('ASSIGNED DATE')
                    ->date()
                    ->placeholder('—'),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListOfficeChildAssigns::route('/'),
            'create' => CreateOfficeChildAssign::route('/create'),
        ];
    }
}
