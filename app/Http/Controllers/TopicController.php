<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Topic;

class TopicController extends Controller
{
    public function index() 
    {
    return response()->json(Topic::all());
    }

    public function store(Request $request) 
    {
        $validated = $request->validate(['name' => 'required|unique:topics']);
        $validated['slug'] = str()->slug($request->name);
        
        $topic = Topic::create($validated);
        return response()->json($topic, 201);
    }
}
