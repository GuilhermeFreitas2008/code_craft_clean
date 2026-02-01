<?php

namespace App\Http\Controllers;

use App\Models\UserCourseProgress;
use App\Models\User;
use App\Models\Course;
use App\Services\CourseProgressService;
use Illuminate\Http\Request;

class UserCourseProgressController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Se for Admin, vê tudo (apenas consulta)
        if ($user->role->name === 'admin') {
            return response()->json(UserCourseProgress::with(['user', 'course'])->get());
        }

        // Se for Aluno, busca os progressos dele
        $progresses = UserCourseProgress::where('user_id', $user->id)
            ->with(['course'])
            ->get();

        // Atualiza os progressos antes de retornar
        $progresses->each(function ($progress) use ($user) {
            if ($progress->course) {
                // Recalcula o progresso
                CourseProgressService::update($user, $progress->course);
            }
        });

        return response()->json(
            UserCourseProgress::where('user_id', $user->id)
                ->with(['course'])
                ->get()
        );
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $progress = UserCourseProgress::with(['user', 'course'])->findOrFail($id);

        if ($user->role->name !== 'admin' && $progress->user_id !== $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json($progress);
    }

    // Cria ou Recalcula o progresso
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        $user = User::findOrFail($data['user_id']);
        $course = Course::findOrFail($data['course_id']);

        $progress = CourseProgressService::update($user, $course);

        return response()->json($progress, 201);
    }

    public function update(Request $request, $id)
    {
        $userCourseProgress = UserCourseProgress::findOrFail($id);
        
        $user = $userCourseProgress->user;
        $course = $userCourseProgress->course;

        $progress = CourseProgressService::update($user, $course);

        return response()->json($progress);
    }

    public function destroy($id)
    {
        UserCourseProgress::findOrFail($id)->delete();
        return response()->json(['message' => 'Progress deleted']);
    }
}
