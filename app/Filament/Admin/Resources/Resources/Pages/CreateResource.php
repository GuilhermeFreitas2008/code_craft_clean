<?php

namespace App\Filament\Admin\Resources\Resources\Pages;

use App\Filament\Admin\Resources\Resources\ResourceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateResource extends CreateRecord
{
    protected static string $resource = ResourceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['type'] ?? '') !== 'link' && !empty($data['url'])) {
            // Extrai o path relativo a partir do URL público
            $url = $data['url'];
            $baseUrl = config('filesystems.disks.supabase_resources.url');
            $path = ltrim(str_replace($baseUrl, '', $url), '/');

            try {
                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                $disk = Storage::disk('supabase_resources');
                $bytes = $disk->size($path);
                $data['size'] = self::formatBytes($bytes);
            } catch (\Exception $e) {
                $data['size'] = null;
            }
        }

        return $data;
    }

    private static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)       return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}