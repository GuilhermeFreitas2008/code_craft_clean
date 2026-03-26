<?php

namespace App\Filament\Admin\Resources\Resources\Schemas;

use App\Models\Resource; // Importante: Importar o teu Model
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select; // Adicionado Select
use Filament\Schemas\Schema;

class ResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                
                // Alterado de TextInput para Select
                Select::make('type')
                    ->options(Resource::getTypes()) // Vai buscar o array ao Model
                    ->required()
                    ->native(false)
                    ->default('other'),

                TextInput::make('size'),
                
                Textarea::make('url')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}