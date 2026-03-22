<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseStorage
{
    protected string $url;
    protected string $key;
    protected string $bucket;

    public function __construct()
    {
        $this->url = rtrim(config('services.supabase.url'), '/');
        $this->key = config('services.supabase.key');
        $this->bucket = config('services.supabase.bucket');
    }

    public function upload(string $path, string $contents, string $contentType = 'image/jpeg'): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => $contentType,
            ])->send('PUT', $this->url . '/storage/v1/object/' . $this->bucket . '/' . $path, [
                'body' => $contents,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Supabase upload failed. Status: ' . $response->status() . ' Body: ' . $response->body());
            throw new \Exception('Upload failed: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Supabase upload exception: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(string $path): bool
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->key,
        ])->delete($this->url . '/storage/v1/object/' . $this->bucket . '/' . $path);

        if ($response->successful() || $response->status() === 404) {
            return true;
        }

        Log::error('Supabase delete failed. Status: ' . $response->status());
        throw new \Exception('Delete failed');
    }

    public function exists(string $path): bool
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->key,
        ])->head($this->url . '/storage/v1/object/' . $this->bucket . '/' . $path);

        return $response->successful();
    }

    public function signedUrl(string $path, int $expiresIn = 3600): string
    {
        // URL pública direta - temporário até resolver assinatura correta
        // Para tornar o bucket público, ative "Public bucket" no Supabase:
        // Storage → avatars → Bucket options → Public bucket ✅
        return $this->url . '/storage/v1/object/public/' . $this->bucket . '/' . $path;
    }
}