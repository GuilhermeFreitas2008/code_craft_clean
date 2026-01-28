<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Lesson;

class UserProgressPolicy
{
    /**
     * MARCAR LIÇÃO COMO CONCLUÍDA
     * Apenas USERS inscritos no curso
     */
    public function complete(User $user, Lesson $lesson): bool
    {
        // ❌ admin não conclui lições
        if ($user->role->name === 'admin') {
            return false;
        }

        // ✅ user só se estiver inscrito
        return $lesson->module
            ->course
            ->enrollments()
            ->where('user_id', $user->id)
            ->exists();
    }
}
