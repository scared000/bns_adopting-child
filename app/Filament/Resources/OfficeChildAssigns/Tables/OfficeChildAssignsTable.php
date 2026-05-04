<?php

namespace App\Filament\Resources\OfficeChildAssigns\Tables;

use App\Models\OfficeChildAssign;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OfficeChildAssignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('child.firstname')
                    ->label('CHILD NAME')
                    ->weight('bold')
                    ->formatStateUsing(fn ($record) => $record->child->firstname . ' ' . $record->child->lastname)
                    ->searchable(),

                TextColumn::make('barangay')
                    ->label('BARANGAY')
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        $bns = $record->bns;
                        return collect([
                            $bns?->purok,
                            $bns?->barangay?->brgyDesc,
                            $bns?->municipality?->citymunDesc,
                            $bns?->province?->provDesc,
                        ])->filter()->implode(', ') ?: '—';
                    }),

                TextColumn::make('office.office')
                    ->label('ASSIGNED OFFICE')
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        $office = $record->office;
                        return collect([
                            $office->office,
                            $office->short_name ? "({$office->short_name})" : null,
                        ])->filter()->implode(' ') ?: '—';
                    })
                    ->searchable(),

                TextColumn::make('bns.firstname')
                    ->label('ASSIGNED BNS')
                    ->formatStateUsing(fn ($record) => $record->bns->firstname . ' ' . $record->bns->lastname)
                    ->searchable(),

                TextColumn::make('assigned_date')
                    ->label('ASSIGNED DATE')
                    ->date()
                    ->placeholder('—'),

                TextColumn::make('visits_count')
                    ->label('VISITS DONE')
                    ->sortable()
                    ->counts('visits')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => $state . ' ' . str('Visit')->plural($state)),

            ])
            ->recordActions([
                EditAction::make('edit')->icon('heroicon-o-pencil'),
                Action::make('unassign')
                    ->label('Unassign')
                    ->icon('heroicon-m-user-minus')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Unassign Child')
                    ->modalDescription('Are you sure you want to remove this child from the BNS? This will free up the child for a new assignment.')
                    ->action(function (OfficeChildAssign $record) {
                        $record->delete();
                        Notification::make()
                            ->title('Child unassigned successfully')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->recordActionsColumnLabel('ACTION')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
