<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Barangay;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
                self::emailField(),
                self::passwordField(),
                TextInput::make('firstname')->required(),
                TextInput::make('middlename'),
                TextInput::make('lastname')->required(),
                TextInput::make('suffix'),
                TextInput::make('purok'),
                Select::make('municipality_id')
                    ->relationship('municipality', 'citymunDesc')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->citymunDesc} ({$record->province->provDesc})")
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(),
                Select::make('barangay_id')
                    ->options(fn (Get $get) =>
                        Barangay::where('citymunCode', $get('municipality_id'))
                            ->pluck('brgyDesc', 'brgyCode')
                    )
                    ->searchable()->required(),
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
