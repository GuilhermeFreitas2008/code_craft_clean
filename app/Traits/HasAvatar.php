<?php
// app/Traits/HasAvatar.php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Services\SupabaseStorage;

trait HasAvatar
{
    public function getAvatarUrlAttribute()
    {
        return $this->getAvatarUrl();
    }
    
    public function getAvatarUrl(): ?string
    {
        if (!$this->avatar_path) {
            return null;
        }
        
        try {
            $storage = new SupabaseStorage();
            // Gerar URL nova sempre que for chamada
            return $storage->signedUrl($this->avatar_path);
        } catch (\Exception $e) {
            Log::error('Failed to generate avatar URL: ' . $e->getMessage());
            return null;
        }
    }
    
    public function uploadAvatar($file): ?string
    {
        try {
            $filename = Str::uuid() . '.jpg';
            $path = "avatars/{$this->id}/{$filename}";
            
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);
            $image->cover(400, 400);
            $encodedImage = $image->toJpeg(85);
            
            $storage = new SupabaseStorage();
            $storage->upload($path, $encodedImage->toString(), 'image/jpeg');
            
            if ($this->avatar_path && $this->avatar_path !== $path) {
                try {
                    $storage->delete($this->avatar_path);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old avatar');
                }
            }
            
            $this->avatar_path = $path;
            $this->save();
            
            return $this->getAvatarUrl();
        } catch (\Exception $e) {
            Log::error('Avatar upload failed: ' . $e->getMessage());
            throw new \Exception('Upload failed');
        }
    }
    
    public function removeAvatar(): bool
    {
        if ($this->avatar_path) {
            try {
                $storage = new SupabaseStorage();
                $storage->delete($this->avatar_path);
            } catch (\Exception $e) {
                Log::warning('Failed to delete avatar');
            }
            
            $this->avatar_path = null;
            $this->save();
        }
        
        return true;
    }
    
    public function hasAvatar(): bool
    {
        return !is_null($this->avatar_path);
    }
}