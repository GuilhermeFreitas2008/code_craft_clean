<?php

namespace App\Filament\Admin\Resources\Resources\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\Resource;

class ResourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge() 
                    ->color('primary')
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->searchable(),

                TextColumn::make('url')
                    ->label('Link/URL')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('primary')
                    ->limit(30)
                    ->copyable()
                    ->tooltip('Clique para copiar a URL'),

                TextColumn::make('size')
                    ->label('Size')
                    ->placeholder('N/A')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                ->label('Type')
                ->options(Resource::getTypes())
                ->native(false)
                ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}