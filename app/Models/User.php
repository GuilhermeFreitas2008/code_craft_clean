<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Campos que podem ser preenchidos automaticamente
     */
    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'role_id',
        'slug'
    ];

    /**
     * Campos que devem ficar ocultos quando o modelo é convertido em array ou JSON
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Casts (transformações automáticas de tipos)
     */
    protected function casts(): array
    {
        return [
            //
        ];
    }

    /**
     * Um utilizador pertence a um role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Um utilizador pode estar inscrito em vários cursos
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Um utilizador pode ter progresso em cursos
     */
    public function courseProgress(): HasMany
    {
        return $this->hasMany(UserCourseProgress::class);
    }

    /**
     * Um utilizador pode ter progresso em várias lições
     */
    public function lessonProgress(): HasMany
    {
        return $this->hasMany(UserProgress::class);
    }
}
