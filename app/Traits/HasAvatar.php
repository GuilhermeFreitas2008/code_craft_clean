<?php

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
            return $storage->signedUrl($this->avatar_path);
        } catch (\Exception $e) {
            Log::error('Failed to generate avatar URL: ' . $e->getMessage());
            return null;
        }
    }
    
    public function uploadAvatar($file): ?string
    {
        try {
            // Guardamos o caminho antigo antes de mudar
            $oldPath = $this->avatar_path;
            
            $filename = Str::uuid() . '.jpg';
            // Dica: 'avatars' aqui costuma ser o nome do bucket ou pasta raiz
            $path = "avatars/{$this->id}/{$filename}";
            
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);
            $image->cover(400, 400);
            $encodedImage = $image->toJpeg(85);
            
            $storage = new SupabaseStorage();
            
            // 1. Faz o upload do novo
            $storage->upload($path, $encodedImage->toString(), 'image/jpeg');
            
            // 2. Atualiza a base de dados
            $this->avatar_path = $path;
            $this->save();
            
            // 3. Só agora apaga o antigo (se existir e for diferente)
            if ($oldPath && $oldPath !== $path) {
                try {
                    $storage->delete($oldPath);
                } catch (\Exception $e) {
                    Log::warning("Failed to delete old avatar at {$oldPath}: " . $e->getMessage());
                }
            }
            
            return $this->getAvatarUrl();
        } catch (\Exception $e) {
            Log::error('Avatar upload failed: ' . $e->getMessage());
            throw new \Exception('Upload failed');
        }
    }
    
    public function removeAvatar(): bool
    {
        if ($this->avatar_path) {
            $pathToDelete = $this->avatar_path;

            try {
                // 1. Primeiro limpamos a base de dados para feedback imediato ao user
                $this->avatar_path = null;
                $this->save();

                // 2. Depois tentamos apagar no Supabase
                $storage = new SupabaseStorage();
                $storage->delete($pathToDelete);
                
                return true;
            } catch (\Exception $e) {
                // Se falhar o delete no bucket, o registo na DB já foi limpo, 
                // apenas logamos para não quebrar a experiência do user.
                Log::warning("Cloud avatar deletion failed for path {$pathToDelete}: " . $e->getMessage());
                return true; 
            }
        }
        
        return true;
    }
    
    public function hasAvatar(): bool
    {
        return !is_null($this->avatar_path);
    }
}