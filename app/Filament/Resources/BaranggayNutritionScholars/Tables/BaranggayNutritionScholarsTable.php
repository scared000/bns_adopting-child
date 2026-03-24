<?php

namespace App\Filament\Resources\BaranggayNutritionScholars\Tables;

use App\Filament\Resources\BaranggayNutritionScholars\BaranggayNutritionScholarsResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class BaranggayNutritionScholarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_path')
                    ->label('PROFILE')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record) =>
                        'https://ui-avatars.com/api/?' . http_build_query([
                            'name'       => $record->firstname . ' ' . $record->lastname,
                            'background' => '6366f1',
                            'color'      => 'fff',
                            'size'       => '128',
                            'bold'       => 'true',
                            'rounded'    => 'true',
                        ])
                    ),
                TextColumn::make('firstname')
                    ->label('BNS NAME')
                    ->searchable()
                    ->weight('bold')
                    ->formatStateUsing(fn ($record) => $record->firstname . ' ' . $record->lastname),

                TextColumn::make('address')
                    ->label('ADDRESS')
                    ->searchable()
                    ->wrap()
                    ->extraAttributes(['class' => 'max-w-xs break-words'])
                    ->getStateUsing(function ($record) {
                        return collect([
                            $record->purok,
                            $record->barangay?->brgyDesc,
                            $record->municipality?->citymunDesc,
                        ])->filter()->implode(', ');
                    }),

                TextColumn::make('child_assignments_count')
                    ->label('CHILD ASSIGNED')
                    ->counts('childAssignments')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => $state . ' ' . str('Child')->plural($state)),

                TextColumn::make('last_visit')
                    ->label('LAST VISIT')
                    ->default('—'),

//                TextColumn::make('status')
//                    ->label('STATUS')
//                    ->searchable()
//                    ->default('—'),

            ])
            ->filters([
                //
            ])
            ->recordUrl(fn ($record) => BaranggayNutritionScholarsResource::getUrl('view', ['record' => $record]))
            ->recordActionsColumnLabel('ACTION')
            ->recordActions([
                EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->badge()
                    ->label('Edit')
                    ->color('info'),

                Action::make('delete')
                    ->label('Delete')
                    ->badge()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation(fn ($record) => $record->childAssignments()->count() === 0)
                    ->modalIcon('heroicon-o-trash')
                    ->modalIconColor('danger')
                    ->modalHeading('Delete baranggay nutrition scholars')
                    ->modalDescription('Are you sure you would like to do this?')
                    ->modalSubmitActionLabel('Delete')
                    ->modalCancelActionLabel('Cancel')
                    ->modalAlignment('center')
                    ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::Center)
                    ->modalWidth('md')
                    ->action(function ($record) {
                        if ($record->childAssignments()->count() > 0) {
                            Notification::make()
                                ->title('Cannot Delete BNS')
                                ->body(
                                    'This BNS has ' . $record->childAssignments()->count() . ' assigned ' .
                                    str('child')->plural($record->childAssignments()->count()) .
                                    '. Please reassign them first.'
                                )
                                ->danger()
                                ->persistent()
                                ->send();

                            return;
                        }

                        $record->delete();

                        Notification::make()
                            ->title('BNS Deleted')
                            ->success()
                            ->send();
                    })
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
