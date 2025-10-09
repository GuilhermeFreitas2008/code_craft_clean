<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        return response()->json(Course::with('modules')->get());
    }

    public function show($id)
    {
        return response()->json(Course::with('modules')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|unique:courses',
            'description' => 'required|string',
            'slug' => 'required|string|unique:courses',
        ]);

        $course = Course::create($data);
        return response()->json($course, 201);
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $data = $request->validate([
            'title' => 'string|unique:courses,title,' . $id,
            'description' => 'string',
            'slug' => 'string|unique:courses,slug,' . $id,
        ]);

        $course->update($data);
        return response()->json($course);
    }

    public function destroy($id)
    {
        Course::findOrFail($id)->delete();
        return response()->json(['message' => 'Course deleted']);
    }
}
