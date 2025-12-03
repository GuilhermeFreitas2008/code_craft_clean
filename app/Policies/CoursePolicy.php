<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    // Admin tem acesso total
    public function before(User $authUser, $ability)
    {
        if ($authUser->role && $authUser->role->name === 'admin') {
            return true;
        }
    }

    // Ver lista de cursos -> qualquer utilizador autenticado pode ver (ou público via rota GET pública)
    public function viewAny(User $authUser)
    {
        return true;
    }

    // Ver um curso específico
    public function view(User $authUser, Course $course)
    {
        // se quiseres tornar público, basta return true
        return true;
    }

    // Criar curso -> só admin (handled by before) -> devolve false para user
    public function create(User $authUser)
    {
        return false;
    }

    // Atualizar curso -> só admin por agora
    public function update(User $authUser, Course $course)
    {
        return false;
    }

    // Apagar curso -> só admin por agora
    public function delete(User $authUser, Course $course)
    {
        return false;
    }
}
