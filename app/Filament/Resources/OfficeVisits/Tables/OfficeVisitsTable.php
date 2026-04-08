<?php

namespace App\Filament\Resources\OfficeVisits\Tables;

use App\Filament\Resources\AdoptedChildren\Tables\AdoptedChildrenTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OfficeVisitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('visit_date', 'desc')
            ->columns([
                TextColumn::make('child.firstname')
                    ->weight('bold')
                    ->label('CHILD NAME')
                    ->formatStateUsing(fn ($record) => $record->child?->firstname . ' ' . $record->child?->lastname ?? '—')
                    ->searchable(),

                TextColumn::make('bns.firstname')
                    ->label('ASSIGNED BNS')
                    ->formatStateUsing(fn ($record) => $record->bns?->firstname . ' ' . $record->bns?->lastname ?? '—')
                    ->searchable(),

                TextColumn::make('office.office')
                    ->label('ASSIGNED OFFICE')
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        $office = $record->office;
                        if (! $office) return '—';
                        return collect([
                            $office->office,
                            $office->short_name ? "({$office->short_name})" : null,
                        ])->filter()->implode(' ') ?: '—';
                    })
                    ->searchable(),

                TextColumn::make('visit_date')
                    ->label('VISIT DATE')
                    ->date()
                    ->placeholder('—'),

                TextColumn::make('visit_address')
                    ->label('VISIT ADDRESS')
                    ->wrap()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('STATUS')
                    ->badge()
                    ->wrap()
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->placeholder('—'),
            ])
            ->filters([])
            ->recordAction('view')
            ->recordActionsColumnLabel('ACTION')
            ->recordActions([
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->badge()
                    ->iconButton()
                    ->modalHeading(fn ($record) => 'Visit — ' . ($record->child?->firstname . ' ' . $record->child?->lastname))
                    ->modalWidth('6xl')
                    ->extraModalFooterActions([
                        EditAction::make()
                            ->icon('heroicon-o-pencil')
                            ->color('primary')
                            ->modalWidth('6xl'),
                    ])
                    ->schema(self::viewSchema()),

                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->badge()
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function viewSchema(): array
    {
        return [
            Section::make('Assignment Information')
                ->icon('heroicon-o-user-group')
                ->columns(2)
                ->schema([
                    TextEntry::make('child.firstname')
                        ->label('Child Name')
                        ->getStateUsing(fn ($record) => $record->child
                            ? $record->child->firstname . ' ' . $record->child->lastname
                            : '—')
                        ->weight('bold'),

                    TextEntry::make('bns.firstname')
                        ->label('Assigned BNS')
                        ->getStateUsing(fn ($record) => $record->bns
                            ? $record->bns->firstname . ' ' . $record->bns->lastname
                            : '—'),

                    TextEntry::make('office.office')
                        ->label('Assigned Office')
                        ->getStateUsing(function ($record) {
                            $office = $record->office;
                            if (! $office) return '—';
                            return collect([
                                $office->office,
                                $office->short_name ? "({$office->short_name})" : null,
                            ])->filter()->implode(' ') ?: '—';
                        })
                        ->columnSpanFull(),
                ]),

            Section::make('Visit Details')
                ->icon('heroicon-o-map-pin')
                ->columns(3)
                ->schema([
                    TextEntry::make('visit_date')
                        ->label('Visit Date')
                        ->date()
                        ->placeholder('—'),

                    TextEntry::make('weight')
                        ->label('Weight')
                        ->suffix(' kg')
                        ->placeholder('—'),

                    TextEntry::make('height')
                        ->label('Height')
                        ->suffix(' cm')
                        ->placeholder('—'),

                    TextEntry::make('status')
                        ->label('Nutritional Status')
                        ->badge()
                        ->color(fn (string $state): string => self::statusColor($state))
                        ->placeholder('—'),

                    TextEntry::make('visit_address')
                        ->label('Visit Address')
                        ->columnSpanFull()
                        ->placeholder('—'),
                ]),

            Section::make('Items Distributed')
                ->icon('heroicon-o-gift')
                ->schema([
                    RepeatableEntry::make('visitItems')
                        ->label('')
                        ->schema([
                            TextEntry::make('Item_description')
                                ->label('Item'),

                            TextEntry::make('item_quantity')
                                ->label('Qty'),

                            TextEntry::make('item_amount')
                                ->label('Amount')
                                ->prefix('₱'),
                        ])
                        ->columns(3),
                ]),

            Section::make('Documentation')
                ->icon('heroicon-o-camera')
                ->schema([
                    ImageEntry::make('visit_documentation')
                        ->label('Visit Photos')
                        ->disk('public')
                        ->height(120)
                        ->square()
                        ->stacked()
                        ->placeholder('No photos uploaded'),
                ]),
        ];
    }

    private static function statusColor(string $state): string
    {
        return AdoptedChildrenTable::statusColor($state);
    }
}
