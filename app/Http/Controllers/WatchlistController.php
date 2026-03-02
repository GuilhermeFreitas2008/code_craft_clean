<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    /**
     * Listar os cursos na watchlist do utilizador autenticado.
     * GET /api/watchlist
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Carrega os cursos da watchlist
        $courses = $user->watchlist()->get();

        return response()->json($courses);
    }

    /**
     * Adicionar um curso à watchlist.
     * POST /api/watchlist/{course}
     */
    public function store(Request $request, Course $course)
    {
        $user = $request->user();

        // Verifica se o curso já está na watchlist
        if ($user->watchlist()->where('course_id', $course->id)->exists()) {
            return response()->json([
                'message' => 'Course already in watchlist'
            ], 409); // 409 Conflict
        }

        // Anexa o curso à watchlist do utilizador
        $user->watchlist()->attach($course->id, [
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'message' => 'Course added to watchlist',
            'course' => $course
        ], 201);
    }

    /**
     * Remover um curso da watchlist.
     * DELETE /api/watchlist/{course}
     */
    public function destroy(Request $request, Course $course)
    {
        $user = $request->user();
        
        // Verifica se o curso está na watchlist
        if (!$user->watchlist()->where('course_id', $course->id)->exists()) {
            return response()->json([
                'message' => 'Course not in watchlist'
            ], 404);
        }

        // Remove da watchlist
        $user->watchlist()->detach($course->id);

        return response()->json([
            'message' => 'Course removed from watchlist'
        ]);
    }

    /**
     * Verificar se um curso específico está na watchlist.
     * GET /api/watchlist/check/{course}
     */
    public function check(Request $request, Course $course)
    {
        $user = $request->user();
        
        $exists = $user->watchlist()->where('course_id', $course->id)->exists();

        return response()->json([
            'in_watchlist' => $exists
        ]);
    }
}