<?php

namespace App\Filament\Admin\Resources\Lessons\Tables;

use App\Models\Course;
use App\Models\Module;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

// IMPORTS V5
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class LessonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('module.course.title')
                    ->label('Course')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('module.title')
                    ->label('Module')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Lesson Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('resources.title')
                    ->label('Resources')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->limitList(2)
                    ->expandableLimitedList() 
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('video_url')
                    ->label('Video URL')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // CONTENT REPOSTO AQUI
                TextColumn::make('content')
                    ->label('Content')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('position')
                    ->label('Position')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('course_module_filter')
                    ->schema([
                        Select::make('course_id')
                            ->label('Course')
                            ->options(Course::pluck('title', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->native(false),

                        Select::make('module_id')
                            ->label('Module')
                            ->placeholder(fn ($get) => $get('course_id') ? 'Select a module' : 'Select a course first')
                            ->options(function ($get) {
                                $courseId = $get('course_id');
                                if (!$courseId) {
                                    return [];
                                }
                                return Module::where('course_id', $courseId)->pluck('title', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->disabled(fn ($get) => !$get('course_id')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['course_id'] ?? null,
                                fn (Builder $query, $courseId): Builder => $query->whereHas('module', fn ($q) => $q->where('course_id', $courseId))
                            )
                            ->when(
                                $data['module_id'] ?? null,
                                fn (Builder $query, $moduleId): Builder => $query->where('module_id', $moduleId)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['course_id'] ?? null) {
                            $course = Course::find($data['course_id']);
                            if ($course) $indicators[] = 'Course: ' . $course->title;
                        }
                        if ($data['module_id'] ?? null) {
                            $module = Module::find($data['module_id']);
                            if ($module) $indicators[] = 'Module: ' . $module->title;
                        }
                        return $indicators;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->icon('heroicon-m-pencil-square'),
                    
                DeleteAction::make()
                    ->color('danger')
                    ->icon('heroicon-m-trash'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->icon('heroicon-m-trash'),
                ]),
            ]);
    }
}