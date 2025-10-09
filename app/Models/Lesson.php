<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = ['module_id', 'title', 'content', 'video_url', 'position', 'slug'];

    /**
     * Uma lição pertence a um módulo
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Progresso dos utilizadores nesta lição
     */
    public function progress(): HasMany
    {
        return $this->hasMany(UserProgress::class);
    }
}
