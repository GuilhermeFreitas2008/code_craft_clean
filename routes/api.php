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

    // Rotas de administrador
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class)->except(['show', 'update']);
        Route::apiResource('courses', CourseController::class)->except(['index', 'show']);
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('modules', ModuleController::class);
        Route::apiResource('lessons', LessonController::class);
    });

    // Rotas acessíveis a qualquer utilizador autenticado (sem role:)
    Route::apiResource('users', UserController::class)->only(['show', 'update']);
    Route::apiResource('courses', CourseController::class)->only(['index', 'show']);

    //não autenticado pois está a ser verificado via policy (Como está a usar policy, não precisa de role:)
    Route::apiResource('enrollments', EnrollmentController::class)->only(['index', 'destroy']);


    // Rotas de progresso e inscrições
    Route::middleware('role:user')->group(function () {
        Route::apiResource('enrollments', EnrollmentController::class)->only(['store']);
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

    return response()->json([
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->username,
            'email' => $user->email,
            'role' => $user->role->name,
        ],
    ]);

});

Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out']);
});
