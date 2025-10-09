<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function index()
    {
        return response()->json(Lesson::with('module')->get());
    }

    public function show($id)
    {
        return response()->json(Lesson::with('module')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'position' => 'required|integer',
            'module_id' => 'required|exists:modules,id',
            'slug' => 'required|string|unique:lessons'
        ]);

        $lesson = Lesson::create($data);
        return response()->json($lesson, 201);
    }

    public function update(Request $request, $id)
    {
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

    public function destroy($id)
    {
        Lesson::findOrFail($id)->delete();
        return response()->json(['message' => 'Lesson deleted']);
    }
}
