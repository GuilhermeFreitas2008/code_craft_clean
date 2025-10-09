<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserCourseProgressController;
use App\Http\Controllers\UserProgressController;

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {
    
    // Apenas administradores
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('courses', CourseController::class);
        Route::apiResource('modules', ModuleController::class);
        Route::apiResource('lessons', LessonController::class);
    });

    // Estudantes → inscrições e progresso
    Route::middleware('role:admin,user')->group(function () {
        Route::apiResource('enrollments', EnrollmentController::class)->only(['store', 'index']);
        Route::apiResource('user-course-progress', UserCourseProgressController::class)->only(['index', 'update']);
        Route::apiResource('user-progress', UserProgressController::class)->only(['index', 'update']);
    });
});
