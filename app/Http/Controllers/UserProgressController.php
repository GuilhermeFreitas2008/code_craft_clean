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
     * MARCAR/DESMARCAR lição como completa (COM BOOLEAN)
     * POST /api/lessons/{lesson}/complete
     */
    public function store(Request $request, Lesson $lesson)
    {
        $this->authorize('complete', [UserProgress::class, $lesson]);

        $user = $request->user();
        
        // Verificar se já existe registo para esta lição
        $progress = UserProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        if ($progress) {
            // Se já existe, INVERTE o valor de completed
            $progress->completed = !$progress->completed;
            $progress->completed_at = $progress->completed ? now() : null;
            $progress->save();
            
            $message = $progress->completed ? 'Lesson marked as completed' : 'Lesson unmarked';
            $completed = $progress->completed;
        } else {
            // Se não existe, CRIA com completed = true
            $progress = UserProgress::create([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'completed' => true,
                'completed_at' => now(),
            ]);
            $message = 'Lesson marked as completed';
            $completed = true;
        }

        // Atualizar progresso do curso
        $course = $lesson->module->course;
        CourseProgressService::update($user, $course);

        return response()->json([
            'message' => $message,
            'completed' => $completed,
            'lesson_progress' => $progress,
        ]);
    }
}