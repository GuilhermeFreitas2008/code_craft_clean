<?php

namespace App\Http\Controllers;

use App\Models\UserProgress;
use Illuminate\Http\Request;

class UserProgressController extends Controller
{
    public function index()
    {
        return response()->json(UserProgress::with(['user', 'lesson'])->get());
    }

    public function show($id)
    {
        return response()->json(UserProgress::with(['user', 'lesson'])->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'lesson_id' => 'required|exists:lessons,id',
            'is_completed' => 'required|boolean',
        ]);

        $progress = UserProgress::create($data);
        return response()->json($progress, 201);
    }

    public function update(Request $request, $id)
    {
        $progress = UserProgress::findOrFail($id);

        $data = $request->validate([
            'is_completed' => 'boolean',
        ]);

        $progress->update($data);
        return response()->json($progress);
    }

    public function destroy($id)
    {
        UserProgress::findOrFail($id)->delete();
        return response()->json(['message' => 'Progress deleted']);
    }
}
