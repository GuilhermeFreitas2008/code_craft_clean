<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    // Campos que podem ser preenchidos automaticamente
    protected $fillable = ['title', 'description', 'slug'];

    /**
     * Um curso tem vários módulos
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }

    /**
     * Um curso tem várias inscrições
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Progresso dos utilizadores neste curso
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserCourseProgress::class);
    }
}
