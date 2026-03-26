<?php

namespace App\Filament\Admin\Resources\Comments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CommentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // CABEÇALHO DO CARD (Autor e Aula)
                TextEntry::make('user.username')
                    ->label('Author')
                    ->weight('bold')
                    ->color('primary')
                    ->extraAttributes([
                        'class' => 'bg-gray-100 dark:bg-gray-800 p-4 rounded-t-xl border-t border-x border-gray-200 dark:border-gray-700',
                    ]),

                TextEntry::make('lesson.title')
                    ->label('Lesson')
                    ->extraAttributes([
                        'class' => 'bg-gray-50 dark:bg-gray-800/50 p-4 border-x border-gray-200 dark:border-gray-700',
                    ]),

                // CORPO DO CARD (O Conteúdo em destaque)
                TextEntry::make('content')
                    ->label('Content')
                    ->columnSpanFull()
                    ->html()
                    ->formatStateUsing(fn ($state) => self::highlightWords($state))
                    ->extraAttributes([
                        'class' => 'p-8 bg-white dark:bg-gray-900 border-x border-b border-gray-200 dark:border-gray-700 shadow-lg rounded-b-xl mb-6 text-lg italic text-gray-700 dark:text-gray-300 leading-relaxed',
                    ]),

                // INFO DE SUPORTE (Likes e Respostas)
                TextEntry::make('likes')
                    ->label('Likes')
                    ->numeric()
                    ->badge()
                    ->color('primary'),

                TextEntry::make('parent_id')
                    ->label('Is Reply To Id')
                    ->numeric()
                    ->placeholder('Main Comment')
                    ->extraAttributes(['class' => 'mt-2']),

                // FOOTER (Datas)
                TextEntry::make('created_at')
                    ->label('Posted At')
                    ->dateTime('d/m/Y H:i')
                    ->extraAttributes(['class' => 'mt-4 opacity-60 text-xs']),

                TextEntry::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->extraAttributes(['class' => 'opacity-60 text-xs']),
            ]);
    }

    /**
     * Lógica para destacar palavras perigosas
     */
    protected static function highlightWords(?string $state): string
    {
        if (! $state) return '-';

        $dangerWords = ['helped', 'spam', 'ofensivo'];
        $formatted = $state;

        foreach ($dangerWords as $word) {
            $formatted = str_ireplace(
                $word,
                "<span class='text-danger-600 font-bold underline bg-danger-50 px-1 rounded'>$word</span>",
                $formatted
            );
        }

        return $formatted;
    }
}