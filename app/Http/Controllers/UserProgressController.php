<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CourseProgressService;
use Illuminate\Support\Facades\Auth;

class UserProgressController extends Controller
{
    /**
     * Buscar progresso do utilizador
     * GET /api/user-progress?course_id=1
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $courseId = $request->query('course_id');

        $query = UserProgress::where('user_id', $user->id);

        // Filtrar por curso se course_id for fornecido
        if ($courseId) {
            $query->whereHas('lesson.module', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            });
        }

        // Opcional: carregar relações para facilitar no frontend
        $query->with(['lesson.module.course']);

        return response()->json($query->get());
    }

    /**
     * Buscar progresso de uma lição específica
     * GET /api/user-progress/{lesson}
     */
    public function show(Lesson $lesson)
    {
        $user = Auth::user();

        $progress = UserProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        if (!$progress) {
            return response()->json([
                'completed' => false,
                'message' => 'Lesson not started'
            ]);
        }

        return response()->json($progress);
    }

    /**
     * Marcar lição como completa
     * POST /api/lessons/{lesson}/complete
     */
    public function store(Request $request, Lesson $lesson)
    {
        $this->authorize('complete', [UserProgress::class, $lesson]);

        $progress = UserProgress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'completed' => true,
                'completed_at' => now(),
            ]
        );

        // Atualizar progresso do curso
        $course = $lesson->module->course;
        CourseProgressService::update($request->user(), $course);

        return response()->json([
            'message' => 'Lesson marked as completed',
            'lesson_progress' => $progress,
        ]);
    }
}