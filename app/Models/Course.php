<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'is_public',
        'is_draft',
        'created_by',
        'category_id',
        'difficulty_id'
    ];

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('position');
    }

    // Relação útil para ir buscar todas as lições do curso diretamente
    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, Module::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function userProgress()
    {
        return $this->hasMany(UserCourseProgress::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function difficulty()
    {
        return $this->belongsTo(Difficulty::class);
    }

    public function topics()
    {
        return $this->belongsToMany(Topic::class);
    }

    public function watchlistedBy()
    {
        return $this->belongsToMany(User::class, 'watchlist');
    }
}
