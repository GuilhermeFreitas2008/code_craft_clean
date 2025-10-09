<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index()
    {
        return response()->json(Enrollment::with(['user', 'course'])->get());
    }

    public function show($id)
    {
        return response()->json(Enrollment::with(['user', 'course'])->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        $enrollment = Enrollment::create($data);
        return response()->json($enrollment, 201);
    }

    public function update(Request $request, $id)
    {
        $enrollment = Enrollment::findOrFail($id);

        $data = $request->validate([
            'user_id' => 'exists:users,id',
            'course_id' => 'exists:courses,id',
        ]);

        $enrollment->update($data);
        return response()->json($enrollment);
    }

    public function destroy($id)
    {
        Enrollment::findOrFail($id)->delete();
        return response()->json(['message' => 'Enrollment deleted']);
    }
}
