<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        return response()->json(Module::with('course')->get());
    }

    public function show($id)
    {
        return response()->json(Module::with('course')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'position' => 'required|integer',
            'course_id' => 'required|exists:courses,id',
            'slug' => 'required|string|unique:modules'
        ]);

        $module = Module::create($data);
        return response()->json($module, 201);
    }

    public function update(Request $request, $id)
    {
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

    public function destroy($id)
    {
        Module::findOrFail($id)->delete();
        return response()->json(['message' => 'Module deleted']);
    }
}
