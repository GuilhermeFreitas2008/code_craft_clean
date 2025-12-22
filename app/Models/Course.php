<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'is_public',
        'is_draft',
        'created_by'
    ];

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserCourseProgress::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
