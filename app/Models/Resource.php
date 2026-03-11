<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'title',
        'type',
        'size',
        'url'
    ];

    protected $casts = [
        'type' => 'string'
    ];

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_resource')
                    ->withTimestamps();
    }
}