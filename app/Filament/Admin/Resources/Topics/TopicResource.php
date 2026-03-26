<?php

namespace App\Filament\Admin\Resources\Topics;

use App\Filament\Admin\Resources\Topics\Pages\CreateTopic;
use App\Filament\Admin\Resources\Topics\Pages\EditTopic;
use App\Filament\Admin\Resources\Topics\Pages\ListTopics;
use App\Filament\Admin\Resources\Topics\Schemas\TopicForm;
use App\Filament\Admin\Resources\Topics\Tables\TopicsTable;
use App\Models\Topic;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TopicResource extends Resource
{
    protected static ?string $model = Topic::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Topics';

    public static function form(Schema $schema): Schema
    {
        return TopicForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TopicsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTopics::route('/'),
            'create' => CreateTopic::route('/create'),
            'edit' => EditTopic::route('/{record}/edit'),
        ];
    }
}
