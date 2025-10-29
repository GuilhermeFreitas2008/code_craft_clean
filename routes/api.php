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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
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

Route::post('/login', function (Request $request) {
    $data = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $data['email'])->first();

    if (! $user || ! Hash::check($data['password'], $user->password_hash)) {
        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('api_token')->plainTextToken;

    return response()->json(['token' => $token, 'role' => $user->role->name]);
});

Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out']);
});
