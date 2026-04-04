<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return parent::form($schema)->components([
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            TextInput::make('roles_display')
                ->label('Role')
                ->afterStateHydrated(function (TextInput $component) {
                    /** @var User $user */
                    $user = $this->getUser();

                    $component->state(
                        $user->roles
                            ->pluck('name')
                            ->map(fn ($name) => str($name)->replace('_', ' ')->title())
                            ->join(', ')
                    );
                })
                ->disabled()
                ->dehydrated(false),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ]);
    }
}
