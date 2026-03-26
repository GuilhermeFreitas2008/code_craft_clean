<?php

namespace App\Filament\Admin\Resources\Modules\Schemas;

use App\Models\Module;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ModuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Wizard\Step::make('Module Information')
                        ->description('Main information and hierarchy')
                        ->schema([
                            Select::make('course_id')
                                ->label('Course')
                                ->relationship('course', 'title')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->disabled(fn ($context) => $context === 'edit')
                                ->dehydrated()
                                ->live() 
                                ->columnSpanFull()
                                ->afterStateUpdated(function ($state, $set) {
                                    if (!$state) return;
                                    // Sugere a próxima posição disponível ao trocar de curso
                                    $next = (Module::where('course_id', $state)->max('position') ?? 0) + 1;
                                    $set('position', $next);
                                }),

                            TextInput::make('title')
                                ->label('Module Title')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),

                            Select::make('position')
                                ->label('Insert at Position')
                                ->required()
                                ->live()
                                ->searchable()
                                // Bloqueia a posição até selecionar um curso
                                ->disabled(fn ($get) => !$get('course_id'))
                                ->options(function ($get, $record) {
                                    $courseId = $get('course_id');
                                    if (!$courseId) return [];

                                    $query = Module::where('course_id', $courseId);
                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }
                                    
                                    $modules = $query->orderBy('position')->get();
                                    $count = $modules->count();
                                    $options = [];

                                    foreach ($modules as $index => $mod) {
                                        $pos = $index + 1;
                                        $options[$pos] = "Position -> {$pos}: Replace '{$mod->title}'";
                                    }
                                    
                                    $lastPos = $count + 1;
                                    $options[$lastPos] = "Position -> {$lastPos}: (At the end)";
                                    
                                    return $options;
                                })
                                ->native(false)
                                ->selectablePlaceholder(false),

                            Hidden::make('slug')->required(),
                        ])->columns(2),
                ])
                ->extraAttributes([
                    'style' => 'max-width: 1000px !important; width: 900px !important; margin-left: auto; margin-right: auto;'
                ])
            ]);
    }
}