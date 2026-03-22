<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser; 
use Filament\Models\Contracts\HasName;
use Filament\Panel; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasAvatar; // ← ADICIONAR esta linha

class User extends Authenticatable implements FilamentUser, HasName 
{
    use HasApiTokens, HasFactory, Notifiable, HasAvatar; // ← ADICIONAR HasAvatar

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'role_id',
        'slug',
        'avatar_path', // ← ADICIONAR avatar_path
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $appends = ['avatar_url']; // ← ADICIONAR para API

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role_id === 1;
    }

    public function getFilamentName(): string
    {
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