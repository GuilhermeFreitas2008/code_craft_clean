<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * LISTAR LIÇÕES
     * Admin → todas
     * User → apenas lições dos cursos onde está inscrito
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role->name === 'admin') {
            return response()->json(
                Lesson::with('module.course')->get()
            );
        }

        return response()->json(
            Lesson::whereHas('module.course.enrollments', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with('module.course')->get()
        );
    }

    /**
     * MOSTRAR LIÇÃO
     * Admin → vê tudo
     * User → só se estiver inscrito no curso
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $lesson = Lesson::with('module.course')->findOrFail($id);

        if ($user->role->name === 'admin') {
            return response()->json($lesson);
        }

        $isEnrolled = $lesson->module
            ->course
            ->enrollments()
            ->where('user_id', $user->id)
            ->exists();

        if ($isEnrolled) {
            return response()->json($lesson);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * CRIAR LIÇÃO
     * Apenas admin
     */
    public function store(Request $request)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'position' => 'required|integer',
            'module_id' => 'required|exists:modules,id',
            'slug' => 'required|string|unique:lessons'
        ]);

        return response()->json(Lesson::create($data), 201);
    }

    /**
     * ATUALIZAR LIÇÃO
     * Apenas admin
     */
    public function update(Request $request, $id)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $lesson = Lesson::findOrFail($id);

        $data = $request->validate([
            'title' => 'string',
            'content' => 'string',
            'position' => 'integer',
            'module_id' => 'exists:modules,id',
            'slug' => 'string|unique:lessons,slug,' . $lesson->id
        ]);

        $lesson->update($data);

        return response()->json($lesson);
    }

    /**
     * APAGAR LIÇÃO
     * Apenas admin
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        Lesson::findOrFail($id)->delete();

        return response()->json(['message' => 'Lesson deleted']);
    }
}
