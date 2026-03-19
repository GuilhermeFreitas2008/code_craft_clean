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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('title')
                    ->label('Título do Curso')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->sortable()
                    ->badge(),

                TextColumn::make('difficulty.name')
                    ->label('Dificuldade')
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                // Mostra um ícone Verde (Check) ou Vermelho (X)
                IconColumn::make('is_public')
                    ->label('Público')
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('is_draft')
                    ->label('Rascunho')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('owner.username')
                    ->label('Criado por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name')
                    ->preload(),
                    
                SelectFilter::make('difficulty_id')
                    ->label('Dificuldade')
                    ->relationship('difficulty', 'name')
                    ->preload(),
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