<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = ['user_id', 'course_id', 'enrolled_at'];

    /**
     * Inscrição pertence a um utilizador
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Inscrição pertence a um curso
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
