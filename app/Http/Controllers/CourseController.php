<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * LISTAR CURSOS
     * Admin → vê TODOS
     * User → só vê is_public = true e is_draft = false
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role->name === 'admin') {
            $courses = Course::with(['category', 'difficulty', 'modules.lessons', 'topics'])
                ->get();
        } else {
            $courses = Course::with(['category', 'difficulty', 'modules.lessons', 'topics'])
                ->where('is_public', true)
                ->where('is_draft', false)
                ->get();
        }

        return response()->json($courses);
    }

    /**
     * MOSTRAR UM CURSO
     * Admin → vê tudo
     * User → só se for público e não draft
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $course = Course::with(['category', 'difficulty', 'modules.lessons', 'topics'])
            ->findOrFail($id);

        if ($user->role->name === 'admin') {
            return response()->json($course);
        }

        if ($course->is_public && !$course->is_draft) {
            return response()->json($course);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * CRIAR CURSO
     * Apenas admin
     */
    public function store(Request $request)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'title' => 'required|string|unique:courses',
            'description' => 'required|string',
            'slug' => 'required|string|unique:courses',
            'category_id' => 'required|exists:categories,id',
            'difficulty_id' => 'required|exists:difficulties,id', 
            'topics' => 'array',
            'topics.*' => 'exists:topics,id',
            'is_public' => 'boolean',
            'is_draft' => 'boolean',
        ]);

        // Guardar quem criou (apenas informativo)
        $data['created_by'] = $request->user()->id;

        $course = Course::create($data);

        if ($request->has('topics')) {
            $course->topics()->sync($request->topics);
        }

        return response()->json($course->load('topics'), 201);
    }

    /**
     * ATUALIZAR CURSO
     * Apenas admin
     */
    public function update(Request $request, $id)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $course = Course::findOrFail($id);

        $data = $request->validate([
            'title' => 'string|unique:courses,title,' . $id,
            'description' => 'string',
            'slug' => 'string|unique:courses,slug,' . $id,
            'category_id' => 'exists:categories,id',
            'difficulty_id' => 'exists:difficulties,id',
            'topics' => 'array',              
            'topics.*' => 'exists:topics,id', 
            'is_public' => 'boolean',
            'is_draft' => 'boolean',
        ]);

        $course->update($data);

       
        if ($request->has('topics')) {
            $course->topics()->sync($request->topics);
        }

        // Devolver o curso com os tópicos carregados para o Postman mostrar tudo certinho
        return response()->json($course->load('topics'));
    }

    /**
     * APAGAR CURSO
     * Apenas admin
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        Course::findOrFail($id)->delete();

        return response()->json(['message' => 'Course deleted']);
    }
}