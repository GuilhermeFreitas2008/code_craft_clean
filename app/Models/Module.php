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

    protected static function booted()
    {
        // Lógica para quando crias um módulo novo
        static::creating(function ($module) {
            static::where('course_id', $module->course_id)
                ->where('position', '>=', $module->position)
                ->increment('position');
        });

        // Lógica para quando mudas a posição de um módulo que já existe
        static::updating(function ($module) {
            if ($module->isDirty('position')) {
                $oldPos = $module->getOriginal('position');
                $newPos = $module->position;

                if ($newPos > $oldPos) {
                    // Moveu para baixo: puxa os que ficaram no meio para cima
                    static::where('course_id', $module->course_id)
                        ->whereBetween('position', [$oldPos + 1, $newPos])
                        ->decrement('position');
                } else {
                    // Moveu para cima: empurra os que ficaram no meio para baixo
                    static::where('course_id', $module->course_id)
                        ->whereBetween('position', [$newPos, $oldPos - 1])
                        ->increment('position');
                }
            }
        });
    }
}
