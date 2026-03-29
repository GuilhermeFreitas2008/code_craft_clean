<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['module_id', 'title', 'content', 'video_url', 'position', 'slug'];

    /**
     * Uma lição pertence a um módulo
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Progresso dos utilizadores nesta lição
     */
    public function progress()
    {
        return $this->hasMany(UserProgress::class);
    }

    public function resources()
    {
        return $this->belongsToMany(Resource::class, 'lesson_resource')
                    ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    protected static function booted()
    {
        static::creating(function ($lesson) {
            // Empurra as aulas existentes para baixo
            static::where('module_id', $lesson->module_id)
                ->where('position', '>=', $lesson->position)
                ->increment('position');
        });

        static::updating(function ($lesson) {
            if ($lesson->isDirty('position')) {
                $oldPos = $lesson->getOriginal('position');
                $newPos = $lesson->position;

                if ($newPos > $oldPos) {
                    static::where('module_id', $lesson->module_id)
                        ->whereBetween('position', [$oldPos + 1, $newPos])
                        ->decrement('position');
                } else {
                    static::where('module_id', $lesson->module_id)
                        ->whereBetween('position', [$newPos, $oldPos - 1])
                        ->increment('position');
                }
            }
        });
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'user_progress')
                    ->wherePivot('completed', true);
    }
}