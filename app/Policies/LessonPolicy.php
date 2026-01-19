<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Lesson;

class LessonPolicy
{
    /**
     * ADMIN → tudo
     */
    public function before(User $user)
    {
        if ($user->role->name === 'admin') {
            return true;
        }
    }

    /**
     * LISTAR lições
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * VER lição
     * User só vê se estiver inscrito no curso da lição
     */
    public function view(User $user, Lesson $lesson): bool
    {
        return $lesson->module
            ->course
            ->enrollments()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return false;
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return false;
    }
}
