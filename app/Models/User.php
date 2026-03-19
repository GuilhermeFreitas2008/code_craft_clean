<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser; 
use Filament\Models\Contracts\HasName; // <-- 1. IMPORTANTE: Adicionado o HasName
use Filament\Panel; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// 2. IMPORTANTE: Adicionado o HasName à lista de implementações
class User extends Authenticatable implements FilamentUser, HasName 
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'role_id',
        'slug'
    ];

    protected $hidden = [
        'password_hash',
    ];

    /**
     * 1. DIZER AO LARAVEL ONDE ESTÁ A PASSWORD
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * 2. DIZER AO FILAMENT QUEM PODE ENTRAR
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role_id === 1;
    }

    /**
     * 3. DIZER AO FILAMENT ONDE ESTÁ O NOME DO UTILIZADOR
     */
    public function getFilamentName(): string
    {
        // Retorna o vosso campo 'username' da base de dados
        return $this->username; 
    }

    protected function casts(): array
    {
        return [
            // ...
        ];
    }

    // --- Relações ---

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function courseProgress()
    {
        return $this->hasMany(UserCourseProgress::class);
    }

    public function lessonProgress()
    {
        return $this->hasMany(UserProgress::class);
    }

    public function watchlist() 
    {
        return $this->belongsToMany(Course::class, 'watchlist')->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function commentLikes()
    {
        return $this->hasMany(CommentLike::class);
    }

    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
    }
}