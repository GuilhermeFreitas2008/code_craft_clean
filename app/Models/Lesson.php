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
}
