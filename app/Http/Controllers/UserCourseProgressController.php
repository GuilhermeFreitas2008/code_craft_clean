<?php

namespace App\Http\Controllers;

use App\Models\UserCourseProgress;
use Illuminate\Http\Request;

class UserCourseProgressController extends Controller
{
    public function index()
    {
        return response()->json(UserCourseProgress::with(['user', 'course'])->get());
    }

    public function show($id)
    {
        return response()->json(UserCourseProgress::with(['user', 'course'])->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'progress' => 'required|numeric|min:0|max:100',
        ]);

        $progress = UserCourseProgress::create($data);
        return response()->json($progress, 201);
    }

    public function update(Request $request, $id)
    {
        $progress = UserCourseProgress::findOrFail($id);

        $data = $request->validate([
            'progress' => 'numeric|min:0|max:100',
        ]);

        $progress->update($data);
        return response()->json($progress);
    }

    public function destroy($id)
    {
        UserCourseProgress::findOrFail($id)->delete();
        return response()->json(['message' => 'Progress deleted']);
    }
}
