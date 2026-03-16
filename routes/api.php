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
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DifficultyController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\CommentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {

    // Rotas de administrador
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class)->except(['show', 'update']);
        Route::apiResource('courses', CourseController::class)->except(['index', 'show']);
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('modules', ModuleController::class)->except(['index', 'show']);
        Route::apiResource('lessons', LessonController::class)->except(['index', 'show']);
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        Route::apiResource('difficulties', DifficultyController::class)->except(['index', 'show']);
        Route::apiResource('topics', TopicController::class)->except(['index', 'show']);
        // Admin pode GERIR recursos das lições
        Route::apiResource('resources', ResourceController::class)->except(['index', 'show']);

        // Admin pode VER progresso dos alunos
        Route::apiResource('user-course-progress', UserCourseProgressController::class)->only(['index', 'show']);
    });

    // Rotas acessíveis a qualquer utilizador autenticado (sem role:)
    Route::apiResource('users', UserController::class)->only(['show', 'update']);
    Route::apiResource('courses', CourseController::class)->only(['index', 'show']);
    Route::apiResource('modules', ModuleController::class)->only(['index', 'show']);
    Route::apiResource('lessons', LessonController::class)->only(['index', 'show']);
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::apiResource('difficulties', DifficultyController::class)->only(['index', 'show']);
    Route::apiResource('topics', TopicController::class)->only(['index', 'show']);

    //não autenticado pois está a ser verificado via policy (Como está a usar policy, não precisa de role:)
    Route::apiResource('enrollments', EnrollmentController::class)->only(['index', 'destroy']);
    
    // Rota para marcar lição como completa
    Route::post(
        '/lessons/{lesson}/complete',
        [UserProgressController::class, 'store']
    );
    
    // Rota para obter progresso do usuário
    Route::get('/user-progress', [UserProgressController::class, 'index']);
    Route::get('/user-progress/{lesson}', [UserProgressController::class, 'show']);

    // Rotas de watchlist
    Route::get('/watchlist', [WatchlistController::class, 'index']);
    Route::post('/watchlist/{course}', [WatchlistController::class, 'store']);
    Route::delete('/watchlist/{course}', [WatchlistController::class, 'destroy']);
    Route::get('/watchlist/check/{course}', [WatchlistController::class, 'check']); // opcional

     // Resources (listar e ver - apenas para inscritos)
    Route::get('/lessons/{lesson}/resources', [ResourceController::class, 'index']);
    Route::get('/resources/{resource}', [ResourceController::class, 'show']);

    // Comments (listar, criar, like)
    Route::get('/lessons/{lesson}/comments', [CommentController::class, 'index']);
    Route::post('/lessons/{lesson}/comments', [CommentController::class, 'store']);
    Route::post('/comments/{comment}/like', [CommentController::class, 'like']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // Rotas de progresso e inscrições
    Route::middleware('role:user')->group(function () {
        Route::apiResource('enrollments', EnrollmentController::class)->only(['store']);
        Route::apiResource('user-course-progress', UserCourseProgressController::class)->only(['index', 'update']);
    });
});

// LOGIN - CORRIGIDO com role_id
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
            'role_id' => $user->role_id,
            'created_at' => $user->created_at,
        ],
    ]);
});

// LOGOUT
Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out']);
});

// REGISTO
Route::post('/register', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255|unique:users,username',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = User::create([
        'username' => $request->name,
        'email' => $request->email,
        'password_hash' => Hash::make($request->password),
        'role_id' => 2, // role_id = 2 para users normais
        'slug' => Str::slug($request->name) . '-' . uniqid(),
    ]);

    $token = $user->createToken('api_token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->username,
            'email' => $user->email,
            'role' => $user->role->name ?? 'user',
            'role_id' => $user->role_id,
            'created_at' => $user->created_at,
        ],
    ], 201);
});