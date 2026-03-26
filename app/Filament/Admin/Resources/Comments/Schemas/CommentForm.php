<?php

namespace App\Filament\Admin\Resources\Comments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('lesson_id')
                    ->required()
                    ->numeric(),
                TextInput::make('parent_id')
                    ->numeric(),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('likes')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
