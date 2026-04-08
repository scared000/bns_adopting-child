<?php

namespace App\Filament\Resources\AdoptedChildren\RelationManagers;

use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImmunizationsChildRelationManager extends RelationManager
{
    protected static string $relationship = 'immunizations';
    protected static ?string $title = 'Immunization Records';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('vaccine_description')
                ->label('Vaccine / Description')
                ->options([
                    'BCG'        => 'BCG',
                    'Hepatitis B' => 'Hepatitis B',
                    'Pentavalent' => 'Pentavalent (DPT-HepB-Hib)',
                    'OPV'        => 'OPV (Oral Polio)',
                    'IPV'        => 'IPV (Inactivated Polio)',
                    'PCV'        => 'PCV (Pneumococcal)',
                    'MMR'        => 'MMR (Measles, Mumps, Rubella)',
                    'MCV'        => 'MCV (Measles-Containing)',
                    'Vitamin A'  => 'Vitamin A Supplementation',
                    'Rotavirus'  => 'Rotavirus',
                    'Influenza'  => 'Influenza',
                    'Other'      => 'Other',
                ])
                ->searchable()
                ->required()
                ->columnSpanFull(),

            DatePicker::make('dose_1')
                ->label('1st Dose Date')
                ->native(false),

            DatePicker::make('dose_2')
                ->label('2nd Dose Date')
                ->native(false),

            DatePicker::make('dose_3')
                ->label('3rd Dose Date')
                ->native(false),

            Textarea::make('remarks')
                ->label('Remarks')
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Immunization Records')
            ->columns([
                TextColumn::make('vaccine_description')
                    ->label('VACCINE')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('dose_1')
                    ->label('1ST DOSE')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('M d, Y') : '—')
                    ->icon(fn ($state) => $state ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle')
                    ->iconColor(fn ($state) => $state ? 'success' : 'gray')
                    ->alignCenter(),

                TextColumn::make('dose_2')
                    ->label('2ND DOSE')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('M d, Y') : '—')
                    ->icon(fn ($state) => $state ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle')
                    ->iconColor(fn ($state) => $state ? 'success' : 'gray')
                    ->alignCenter(),

                TextColumn::make('dose_3')
                    ->label('3RD DOSE')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('M d, Y') : '—')
                    ->icon(fn ($state) => $state ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle')
                    ->iconColor(fn ($state) => $state ? 'success' : 'gray')
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('STATUS')
                    ->badge()
                    ->color(fn (string $state) => $state === 'complete' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                TextColumn::make('remarks')
                    ->label('REMARKS')
                    ->limit(40)
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()->label('Add Record'),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->badge(),
                DeleteAction::make()->iconButton()->badge(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
