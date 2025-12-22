<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    // ADMIN tem acesso total
    public function before(User $authUser, $ability)
    {
        if ($authUser->role && $authUser->role->name === 'admin') {
            return true;
        }
    }

    public function viewAny(User $authUser)
    {
        return true;
    }

    public function view(User $authUser, Course $course)
    {
        return true;
    }

    public function create(User $authUser)
    {
        return false; // apenas admin
    }

    public function update(User $authUser, Course $course)
    {
        return false; // apenas admin
    }

    public function delete(User $authUser, Course $course)
    {
        return false; // apenas admin
    }
}
