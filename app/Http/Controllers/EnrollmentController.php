<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Course;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * LISTAR INSCRIÇÕES
     * Admin → todas
     * User → só as suas
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Admin vê todas as inscrições
        if ($user->role->name === 'admin') {
            return response()->json(
                Enrollment::with(['user', 'course'])->get()
            );
        }

        // User vê apenas as suas
        return response()->json(
            Enrollment::with(['course'])
                ->where('user_id', $user->id)
                ->get()
        );
    }

    /**
     * INSCREVER NUM CURSO
     */
    public function store(Request $request)
    {
        // 1️⃣ Validar request
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        // 2️⃣ Buscar curso
        $course = Course::findOrFail($request->course_id);

        // 3️⃣ Bloquear cursos privados ou draft
        if (!$course->is_public || $course->is_draft) {
            return response()->json([
                'error' => 'You cannot enroll in this course'
            ], 403);
        }

        // 4️⃣ Evitar inscrições duplicadas
        $alreadyEnrolled = Enrollment::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json([
                'error' => 'User already enrolled in this course'
            ], 409);
        }

        // 5️⃣ Criar inscrição
        $enrollment = Enrollment::create([
            'user_id'   => $request->user()->id,
            'course_id' => $course->id,
        ]);

        return response()->json($enrollment, 201);
    }

    /**
     * APAGAR INSCRIÇÃO
     * User → só a sua
     * Admin → qualquer uma
     */
    public function destroy(Enrollment $enrollment)
    {
    $this->authorize('delete', $enrollment);

    $enrollment->delete();

    return response()->json([
        'message' => 'Enrollment deleted successfully'
    ]);
    }

}
