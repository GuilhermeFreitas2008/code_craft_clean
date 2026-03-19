<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password_hash')
                    ->password()
                    ->required(),
                TextInput::make('role_id')
                    ->required()
                    ->numeric(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('avatar'),
            ]);
    }
}
