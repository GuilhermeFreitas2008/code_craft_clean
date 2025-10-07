<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    // Campos que podem ser preenchidos automaticamente
    protected $fillable = ['course_id', 'title', 'description', 'position'];

    /**
     * Um módulo pertence a um curso
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Um módulo tem várias aulas (lessons)
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}
