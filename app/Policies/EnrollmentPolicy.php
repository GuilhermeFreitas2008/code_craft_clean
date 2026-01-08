<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Enrollment;

class EnrollmentPolicy
{
    /**
     * LISTAR INSCRIÇÕES
     * Admin → todas
     * User → só as suas (o filtro é feito no controller)
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role->name, ['admin', 'user']);
    }

    /**
     * APAGAR INSCRIÇÃO
     * Admin → qualquer uma
     * User → apenas a própria
     */
    public function delete(User $user, Enrollment $enrollment): bool
    {
        if ($user->role->name === 'admin') {
            return true;
        }

        return $enrollment->user_id === $user->id;
    }
}
