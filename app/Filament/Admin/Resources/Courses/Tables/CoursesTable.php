<?php

namespace App\Filament\Admin\Resources\Courses\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('title')
                    ->label('Course Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->badge(),

                TextColumn::make('difficulty.name')
                    ->label('Difficulty')
                    ->sortable()
                    ->badge(),

                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean()
                    ->trueColor('primary')
                    ->falseColor('gray'),
                    

                IconColumn::make('is_draft')
                    ->label('Draft')
                    ->boolean()
                    ->trueColor('primary')
                    ->falseColor('gray'),

                TextColumn::make('owner.username')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('difficulty_id')
                    ->label('Difficulty')
                    ->relationship('difficulty', 'name')
                    ->searchable()
                    ->preload(),

                \Filament\Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Public Status')
                    ->placeholder('All Courses')
                    ->trueLabel('Only Public')
                    ->falseLabel('Only Drafts')
                    ->queries(
                        true: fn ($query) => $query->where('is_public', true),
                        false: fn ($query) => $query->where('is_public', false),
                    ),
            ])
            ->recordActions([
                // Apenas com cor e ícone (sem fundo sólido)
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