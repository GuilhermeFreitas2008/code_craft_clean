<?php

namespace App\Filament\Admin\Resources\Lessons\Schemas;

use App\Models\Lesson;
use App\Models\Module;
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
        // Truque para o Intelephense ignorar a existência da classe deprecated
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

                            // GUIA ATUALIZADO BASEADO NO TEU MARKDOWN.TS
                            $placeholderClass::make('formatting_guide')
                                ->label('📖 Guia de Formatação (Clique para ver)')
                                ->content(fn () => new HtmlString('
                                    <details class="cursor-pointer bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <summary class="font-bold text-sm text-primary-600 outline-none">Comandos suportados (Markdown-it & Highlight.js)</summary>
                                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6 text-[11px] leading-relaxed border-t pt-4">
                                            <div>
                                                <p class="font-bold text-gray-700 mb-2 uppercase italic underline">Markdown Base</p>
                                                <code># H1</code> | <code>## H2</code> | <code>### H3</code><br>
                                                <code>**Negrito**</code> | <code>*Itálico*</code><br>
                                                <code>> Citação</code> | <code>- Lista</code>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-700 mb-2 uppercase italic underline">Highlight.js</p>
                                                <p class="mb-1 text-gray-500">Usa três crases + linguagem:</p>
                                                <code class="block bg-gray-200 p-1 rounded mb-1">```php</code>
                                                <code class="block bg-gray-200 p-1 rounded mb-1">```javascript</code>
                                                <code class="block bg-gray-200 p-1 rounded">```sql</code>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-700 mb-2 uppercase italic underline">Ativo no Projeto</p>
                                                <ul class="list-disc ml-3 space-y-1 text-gray-600">
                                                    <li>🔗 <b>Links:</b> Colar a URL ativa o clique.</li>
                                                    <li>🖼️ <b>Imagens:</b> Cantos arredondados (auto).</li>
                                                    <li>🌐 <b>HTML:</b> Podes usar tags HTML puras.</li>
                                                    <li>📋 <b>Código:</b> Gera bloco <code>hljs</code>.</li>
                                                </ul>
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
                ])
                ->extraAttributes([
                    'style' => 'max-width: 1000px !important; width: 900px !important; margin-left: auto; margin-right: auto;'
                ])
            ]);
    }
}