<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CourseProgressService;

class UserProgressController extends Controller
{
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

        // atualizar progresso do curso
        $course = $lesson->module->course;
        CourseProgressService::update($request->user(), $course);

        return response()->json([
            'message' => 'Lesson marked as completed',
            'lesson_progress' => $progress,
        ]);
    }
}

