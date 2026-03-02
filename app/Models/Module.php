<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    // Campos que podem ser preenchidos automaticamente
    protected $fillable = ['course_id', 'title', 'description', 'position', 'slug'];

    /**
     * Um módulo pertence a um curso
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Um módulo tem várias aulas (lessons)
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }
}
