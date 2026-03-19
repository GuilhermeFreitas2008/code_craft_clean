<?php

namespace App\Filament\Admin\Resources\Courses\Schemas;

use Filament\Schemas\Schema; 
use Filament\Schemas\Components\Section; // <-- O SEGREDO ESTÁ AQUI! (Schemas e não Forms)

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Auth;

class CourseForm
{
    public static function configure(Schema $schema): Schema 
    {
        return $schema
            ->components([ // No Schema usa-se ->components()
                Section::make('Detalhes do Curso')
                    ->description('Informações principais e categorização.')
                    ->schema([ // Dentro da Section continua a ser ->schema()
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255),

                        Select::make('category_id')
                            ->label('Categoria')
                            ->relationship('category', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),

                        Select::make('difficulty_id')
                            ->label('Dificuldade')
                            ->relationship('difficulty', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                            
                        Select::make('created_by')
                            ->label('Criado por')
                            ->relationship('owner', 'username')
                            ->default(fn () => Auth::id())
                            ->searchable()
                            ->required(),
                    ])->columns(2),

                Section::make('Visibilidade e Estado')
                    ->schema([
                        Toggle::make('is_public')
                            ->label('Curso Público')
                            ->default(false),
                            
                        Toggle::make('is_draft')
                            ->label('Em Rascunho')
                            ->default(true),
                    ])->columns(2),

                Section::make('Conteúdo')
                    ->schema([
                        Textarea::make('description')
                            ->label('Descrição')
                            ->required()
                            ->rows(5),
                    ]),
            ]);
    }
}