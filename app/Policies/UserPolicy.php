<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Antes de qualquer método, se for admin devolve true (acesso total).
     * Isto evita ter de repetir "is admin" em cada método.
     */
    public function before(User $authUser, $ability)
    {
        // assumimos que a relação role foi carregada, ou podemos usar $authUser->role->name
        if ($authUser->role && $authUser->role->name === 'admin') {
            return true; // admin pode tudo
        }
        // se devolver null, a avaliação segue para os métodos abaixo
    }

    // Ver se um utilizador pode ver a lista de users
    public function viewAny(User $authUser)
    {
        // apenas admins (handled by before), mas mantemos por claridade:
        return false;
    }

    // Ver o próprio user (perfil)
    public function view(User $authUser, User $user)
    {
        // users podem ver o seu próprio perfil
        return $authUser->id === $user->id;
    }

    // Criar user: permitimos apenas admin (handled by before), devolvemos false para user normal
    public function create(User $authUser)
    {
        return false;
    }

    // Atualizar: admin faz tudo (before). user pode actualizar o próprio perfil
    public function update(User $authUser, User $user)
    {
        return $authUser->id === $user->id;
    }

    // Apagar: só admin (handled by before)
    public function delete(User $authUser, User $user)
    {
        return false;
    }
}
