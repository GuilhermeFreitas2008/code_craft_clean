<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     * LISTAR MÓDULOS
     * Admin → todos
     * User → apenas módulos dos cursos onde está inscrito
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role->name === 'admin') {
            return response()->json(
                Module::with('course')->get()
            );
        }

        return response()->json(
            Module::whereHas('course.enrollments', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with('course')->get()
        );
    }

    /**
     * MOSTRAR MÓDULO
     * Admin → vê tudo
     * User → só se estiver inscrito no curso
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $module = Module::with('course')->findOrFail($id);

        if ($user->role->name === 'admin') {
            return response()->json($module);
        }

        $isEnrolled = $module->course
            ->enrollments()
            ->where('user_id', $user->id)
            ->exists();

        if ($isEnrolled) {
            return response()->json($module);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * CRIAR MÓDULO
     * Apenas admin
     */
    public function store(Request $request)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'position' => 'required|integer',
            'course_id' => 'required|exists:courses,id',
            'slug' => 'required|string|unique:modules'
        ]);

        return response()->json(Module::create($data), 201);
    }

    /**
     * ATUALIZAR MÓDULO
     * Apenas admin
     */
    public function update(Request $request, $id)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $module = Module::findOrFail($id);

        $data = $request->validate([
            'title' => 'string',
            'description' => 'string',
            'position' => 'integer',
            'course_id' => 'exists:courses,id',
            'slug' => 'string|unique:modules,slug,' . $module->id
        ]);

        $module->update($data);

        return response()->json($module);
    }

    /**
     * APAGAR MÓDULO
     * Apenas admin
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        Module::findOrFail($id)->delete();

        return response()->json(['message' => 'Module deleted']);
    }
}
