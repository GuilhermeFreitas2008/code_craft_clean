<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->extraAttributes(['style' => 'max-width: none !important; width: 900px !important;'])
                    ->schema([

                        Section::make('User Details')
                            ->schema([
                                TextInput::make('username')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state)))
                                    ->disabled(fn ($context) => $context === 'edit'),

                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->disabled(fn ($context) => $context === 'edit'),

                                TextInput::make('password_hash')
                                    ->label('Password')
                                    ->password()
                                    ->revealable()
                                    ->required(fn ($context) => $context === 'create')
                                    ->hidden(fn ($context) => $context === 'edit'),

                                Select::make('role_id')
                                    ->label('Role')
                                    ->relationship('role', 'name')
                                    ->required()
                                    ->preload(),
                            ])
                            ->columnSpan(2),

                        Section::make('Avatar')
                            ->schema([
                                FileUpload::make('avatar_path')
                                    ->label(false)
                                    ->disk('supabase')
                                    ->directory('avatars')
                                    ->image()
                                    ->imageEditor()
                                    ->imageAspectRatio('1:1')
                                    ->panelAspectRatio('1:1')
                                    ->panelLayout('integrated')
                                    ->disabled(fn ($context) => $context === 'edit'),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}