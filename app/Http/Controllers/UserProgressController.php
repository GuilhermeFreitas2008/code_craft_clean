<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserProgressController extends Controller
{
    public function store(Request $request, Lesson $lesson)
    {
        // 🔐 policy
        $this->authorize('complete', [UserProgress::class, $lesson]);

        // 🔁 evitar duplicados
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

        return response()->json([
            'message' => 'Lesson marked as completed',
            'progress' => $progress
        ]);
    }
}
