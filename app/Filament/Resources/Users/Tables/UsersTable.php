<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use STS\FilamentImpersonate\Actions\Impersonate;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::columns())
            ->filters(self::filters())
            ->recordActions(self::recordActions())
            ->toolbarActions(self::toolbarActions());
    }

    private static function columns(): array
    {
        return [
            self::nameColumn(),
            self::emailColumn(),
            self::rolesColumn(),
            self::emailVerifiedColumn(),
            self::createdAtColumn(),
        ];
    }

    private static function nameColumn(): TextColumn
    {
        return TextColumn::make('name')
            ->formatStateUsing(fn ($record) => $record->firstname . ' ' . $record->lastname)
            ->searchable()
            ->sortable();
    }

    private static function emailColumn(): TextColumn
    {
        return TextColumn::make('email')
            ->searchable()
            ->sortable();
    }

    private static function rolesColumn(): TextColumn
    {
        return TextColumn::make('roles.name')
            ->label('Roles')
            ->badge()
            ->separator(',')
            ->formatStateUsing(
                fn (string $state): string => str($state)
                    ->replace('_', ' ')
                    ->title()
            )
            ->color('primary');
    }

    private static function emailVerifiedColumn(): TextColumn
    {
        return TextColumn::make('email_verified_at')
            ->label('Verified')
            ->dateTime()
            ->sortable()
            ->placeholder('Not verified')
            ->toggleable(isToggledHiddenByDefault: true);
    }

    private static function createdAtColumn(): TextColumn
    {
        return TextColumn::make('created_at')
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    private static function filters(): array
    {
        return [
            SelectFilter::make('roles')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload()
                ->label('Filter by Role'),
        ];
    }


    private static function recordActions(): array
    {
        return [
            Impersonate::make()
                ->iconButton()
                ->redirectTo(fn ($record) => $record->getImpersonateRedirectTo()),
            EditAction::make()
                ->iconButton()
                ->icon('heroicon-o-pencil')
                ->color('info'),

            DeleteAction::make()
                ->iconButton(),
        ];
    }

    private static function toolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }
}
