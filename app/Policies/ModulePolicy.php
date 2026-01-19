<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Module;

class ModulePolicy
{
    /**
     * ADMIN → acesso total
     */
    public function before(User $user)
    {
        if ($user->role->name === 'admin') {
            return true;
        }
    }

    /**
     * LISTAR módulos
     * (podes filtrar depois no controller se quiseres)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * VER módulo
     * User só vê se estiver inscrito no curso
     */
    public function view(User $user, Module $module): bool
    {
        return $module->course
            ->enrollments()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Module $module): bool
    {
        return false;
    }

    public function delete(User $user, Module $module): bool
    {
        return false;
    }
}
