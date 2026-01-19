<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Lesson;
use App\Policies\UserPolicy;
use App\Policies\CoursePolicy;
use App\Policies\EnrollmentPolicy;
use App\Policies\ModulePolicy;
use App\Policies\LessonPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use PhpParser\Node\Expr\AssignOp\Mod;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Course::class => CoursePolicy::class,
        Enrollment::class => EnrollmentPolicy::class,
        Module::class => ModulePolicy::class,
        Lesson::class => LessonPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
