<?php

namespace App\Filament\Admin\Resources\Courses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Wizard\Step::make('Details')
                        ->description('Main information and visibility')
                        ->schema([
                            // 1. Movemos os campos invisíveis para cima para não quebrarem o Grid no final
                            Hidden::make('slug')->required(),
                            Hidden::make('is_draft')->default(true),

                            TextInput::make('title')
                                ->label('Title')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),

                            Select::make('category_id')
                                ->label('Category')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('topics')
                                ->label('Topics')
                                ->relationship('topics', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('difficulty')
                                ->label('Difficulty')
                                ->options([
                                    'beginner' => 'Beginner',
                                    'intermediate' => 'Intermediate',
                                    'advanced' => 'Advanced',
                                ])
                                ->native(false)
                                ->required(),

                            Select::make('created_by')
                                ->label('Creator')
                                ->relationship('owner', 'username')
                                ->default(Auth::id())
                                ->disabled()                  
                                ->dehydrated(fn ($context) => $context === 'create')
                                ->required(),

                            Toggle::make('is_public')
                                ->label('Publish Course')
                                ->helperText('If disabled, it stays as a draft.')
                                ->live()
                                ->afterStateUpdated(fn ($state, $set) => $set('is_draft', !$state))
                                ->inline(false)
                                ->extraAttributes([
                                'style' => 'margin-top: 5px;', 
                                ]),

                        ])->columns(2),

                    Wizard\Step::make('Content')
                        ->description('Course description')
                        ->schema([
                            Textarea::make('description')
                                ->required()
                                ->rows(5)
                                ->columnSpanFull(),
                        ]),
                ])
                ->extraAttributes([
                    'style' => 'max-width: 1000px !important; width: 900px !important; margin-left: auto; margin-right: auto;'
                ])
            ]);
    }
}