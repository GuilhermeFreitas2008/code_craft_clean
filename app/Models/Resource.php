<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    protected $fillable = [
        'title',
        'type',
        'size',
        'url'
    ];

    protected $casts = [
        'type' => 'string'
    ];

    protected static function booted(): void
    {
        static::deleting(function (Resource $resource) {
            if ($resource->type !== 'link' && !empty($resource->url)) {
                $baseUrl = config('filesystems.disks.supabase_resources.url');
                $path = ltrim(str_replace($baseUrl, '', $resource->url), '/');

                try {
                    Storage::disk('supabase_resources')->delete($path);
                } catch (\Exception $e) {
                    // Falha silenciosa — o registo da BD apaga na mesma
                }
            }
        });
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_resource')
                    ->withTimestamps();
    }

    public static function getTypes(): array
    {
        return [
            'pdf'          => 'PDF',
            'image'        => 'Image',
            'presentation' => 'Presentation',
            'archive'      => 'Archive (ZIP/RAR)',
            'link'         => 'Link/URL',
            'other'        => 'Other',
        ];
    }
}