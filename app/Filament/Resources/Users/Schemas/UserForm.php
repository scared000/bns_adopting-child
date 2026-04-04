<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            self::userInformationSection(),
            self::rolesSection(),
        ]);
    }

    private static function userInformationSection(): Section
    {
        return Section::make('User Information')
            ->schema([
                self::nameField(),
                self::emailField(),
                self::passwordField(),
            ])
            ->columns(2);
    }

    private static function rolesSection(): Section
    {
        return Section::make('Roles & Permissions')
            ->description('Assign roles to control what this user can access.')
            ->schema([
                self::rolesField(),
            ]);
    }

    private static function nameField(): TextInput
    {
        return TextInput::make('name')
            ->required()
            ->maxLength(255);
    }

    private static function emailField(): TextInput
    {
        return TextInput::make('email')
            ->email()
            ->required()
            ->unique(ignoreRecord: true)
            ->maxLength(255);
    }

    private static function passwordField(): TextInput
    {
        return TextInput::make('password')
            ->password()
            ->revealable()
            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
            ->dehydrated(fn ($state) => filled($state))
            ->required(fn (string $operation): bool => $operation === 'create')
            ->label(fn (string $operation) => $operation === 'edit'
                ? 'New Password (leave blank to keep)'
                : 'Password'
            )
            ->maxLength(255);
    }

    private static function rolesField(): CheckboxList
    {
        return CheckboxList::make('roles')
            ->relationship('roles', 'name')
            ->columns(2)
            ->searchable()
            ->bulkToggleable()
            ->getOptionLabelFromRecordUsing(
                fn (Role $record) => str($record->name)
                    ->replace('_', ' ')
                    ->title()
            );
    }
}
