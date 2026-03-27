<?php

namespace App\Filament\Admin\Resources\Lessons\Schemas;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

class LessonForm
{
    public static function configure(Schema $schema): Schema
    {
        $placeholderClass = 'Filament\Forms\Components\Placeholder';

        return $schema
            ->components([
                Wizard::make([
                    Wizard\Step::make('Hierarchy')
                        ->description('Select the course and module')
                        ->schema([
                            Select::make('course_id')
                                ->label('Filter by Course')
                                ->relationship('module.course', 'title')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                ->disabled(fn ($context) => $context === 'edit')
                                ->dehydrated(false)
                                ->columnSpanFull(),

                            Select::make('module_id')
                                ->label('Module')
                                ->options(function ($get) {
                                    $courseId = $get('course_id');
                                    return $courseId ? Module::where('course_id', $courseId)->pluck('title', 'id') : [];
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->disabled(fn ($get, $context) => !$get('course_id') || $context === 'edit')
                                ->afterStateUpdated(function ($state, $set) {
                                    if (!$state) return;
                                    $next = (Lesson::where('module_id', $state)->max('position') ?? 0) + 1;
                                    $set('position', $next);
                                }),

                            Select::make('position')
                                ->label('Insert at Position')
                                ->required()
                                ->live()
                                ->searchable()
                                ->disabled(fn ($get) => ! $get('module_id'))
                                ->options(function ($get, $record) {
                                    $moduleId = $get('module_id');
                                    if (!$moduleId) return [];
                                    $query = Lesson::where('module_id', $moduleId);
                                    if ($record) $query->where('id', '!=', $record->id);
                                    $lessons = $query->orderBy('position')->get();
                                    $count = $lessons->count();
                                    $options = [];
                                    foreach ($lessons as $index => $less) {
                                        $pos = $index + 1;
                                        $options[$pos] = "Position -> {$pos}: Replace '{$less->title}'";
                                    }
                                    $options[$count + 1] = "Position -> " . ($count + 1) . ": (At the end)";
                                    return $options;
                                })
                                ->native(false),
                        ])->columns(2),

                    Wizard\Step::make('Content')
                        ->description('Lesson details and code blocks')
                        ->schema([
                            TextInput::make('title')
                                ->label('Lesson Title')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set, $context) => $context === 'create' ? $set('slug', Str::slug($state)) : null),

                            TextInput::make('video_url')
                                ->label('Video URL')
                                ->url(),

                            $placeholderClass::make('formatting_guide')
                                ->label('📖 Guia de Formatação')
                                ->content(fn () => new HtmlString('
                                    <details class="cursor-pointer outline-none group">
                                        <summary class="font-bold text-sm text-primary-600 hover:underline">
                                            Comandos Suportados (Markdown Extendido | Blocos de Código)
                                        </summary>
                                        
                                        <div style="margin-top: 50px; margin-bottom: 30px;">
                                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 60px;">
                                                <div>
                                                    <p style="font-weight: 800; color: #9ca3af; text-transform: uppercase; font-size: 11px; margin-bottom: 25px; letter-spacing: 1.5px;">Markdown Básico</p>
                                                    <div style="font-size: 14px; line-height: 2.5;">
                                                        # Título H1<br>## Subtítulo H2<br>### Título H3<br>**Negrito** e *Itálico*<br>~~Texto tachado~~
                                                    </div>
                                                </div>
                                                <div>
                                                    <p style="font-weight: 800; color: #9ca3af; text-transform: uppercase; font-size: 11px; margin-bottom: 25px; letter-spacing: 1.5px;">Markdown Avançado</p>
                                                    <div style="font-size: 14px; line-height: 2.5;">
                                                        > Citação em destaque<br>- Lista com marcadores<br>1. Lista numerada<br>[Texto do Link](URL)
                                                    </div>
                                                </div>
                                                <div>
                                                    <p style="font-weight: 800; color: #9ca3af; text-transform: uppercase; font-size: 11px; margin-bottom: 25px; letter-spacing: 1.5px;">Blocos de Código</p>
                                                    <div style="font-size: 14px; line-height: 2.5; font-family: monospace;">
                                                        ```php<br>```javascript<br>```sql<br>```html<br>
                                                        <span style="color: #9ca3af; font-family: sans-serif; font-size: 12px; font-weight: bold; text-transform: uppercase; display: block; margin-top: 6px;">(FECHAR SEMPRE COM ```)</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </details>
                                '))
                                ->columnSpanFull(),

                            Textarea::make('content')
                                ->label('Content')
                                ->rows(12)
                                ->required()
                                ->columnSpanFull(),

                            Hidden::make('slug')->required(),
                        ]),

                    // NOVO PASSO ADICIONADO AQUI
                    Wizard\Step::make('Resources')
                        ->description('Manage lesson attachments')
                        ->icon('heroicon-m-paper-clip')
                        ->schema([
                            Select::make('resources')
                                ->label('Attach Resources')
                                ->relationship('resources', 'title')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->live()
                                ->native(false)
                                // Formata o label para aparecer "Título - TIPO"
                                ->getOptionLabelFromRecordUsing(fn (Resource $record) => "{$record->title} - " . strtoupper($record->type))
                                ->columnSpanFull(),
                            
                            $placeholderClass::make('resources_help')
                                ->content('Select existing resources to attach to this lesson. You can search by title or resource type.'),
                        ]),
                ])
                ->extraAttributes([
                    'style' => 'max-width: 1000px !important; width: 900px !important; margin-left: auto; margin-right: auto;'
                ])
            ]);
    }
}