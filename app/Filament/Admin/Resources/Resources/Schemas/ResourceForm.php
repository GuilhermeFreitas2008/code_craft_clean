<?php

namespace App\Filament\Admin\Resources\Resources\Schemas;

use App\Models\Resource;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),

                Select::make('type')
                    ->options(Resource::getTypes())
                    ->required()
                    ->native(false)
                    ->default('other')
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('url', null);
                        $set('size', null);
                        $set('file', null);
                    }),

                // Só aparece para 'link'
                Textarea::make('url')
                    ->required()
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => $get('type') === 'link'),

                // Aparece para todos os outros tipos
                FileUpload::make('url')
                    ->label('File')
                    ->required()
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => filled($get('type')) && $get('type') !== 'link')
                    ->disk('supabase_resources')
                    ->directory('uploads')
                    ->visibility('public')
                    ->acceptedFileTypes(fn (Get $get): array => match ($get('type')) {
                        'pdf'          => ['application/pdf'],
                        'image'        => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
                        'presentation' => [
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        ],
                        'archive'      => [
                            'application/zip',
                            'application/x-rar-compressed',
                            'application/x-zip-compressed',
                            'application/x-7z-compressed',
                        ],
                        default => [],
                    })
                    ->maxSize(51200)
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, Set $set): string {
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension    = $file->getClientOriginalExtension();
                        $safeName     = Str::slug($originalName) . '.' . $extension;

                        $path = 'uploads/' . $safeName;
                        if (Storage::disk('supabase_resources')->exists($path)) {
                            $path = 'uploads/' . Str::slug($originalName) . '-' . uniqid() . '.' . $extension;
                        }

                        Storage::disk('supabase_resources')->put(
                            $path,
                            $file->get(),
                            ['visibility' => 'public']
                        );

                        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                        $disk = Storage::disk('supabase_resources');

                        return $disk->url($path);
                    }),
            ]);
    }
}