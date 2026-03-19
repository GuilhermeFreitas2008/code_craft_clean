<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

// IMPORTANTE: NO FILAMENT V5 AS ACTIONS SÃO TODAS IMPORTADAS DAQUI
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), 

                TextColumn::make('username')
                    ->label('Nome de Utilizador')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable()
                    ->copyable() // Ícone para copiar o email rapidamente
                    ->toggleable(),

                TextColumn::make('role.name') 
                    ->label('Cargo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Admin' => 'success', 
                        'Student' => 'info',  
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('avatar')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Data de Registo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Última Atualização')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role_id')
                    ->label('Filtrar por Cargo')
                    ->relationship('role', 'name')
                    ->preload(),
            ])
            ->recordActions([ // SINTAXE CORRETA DO V5
                EditAction::make()
                    
                    ->icon('heroicon-m-pencil-square'),
                    
                DeleteAction::make()
                   
                    ->color('danger')
                    ->icon('heroicon-m-trash'),
            ])
            ->toolbarActions([ // SINTAXE CORRETA DO V5
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->icon('heroicon-m-trash'),
                ]),
            ]);
    }
}