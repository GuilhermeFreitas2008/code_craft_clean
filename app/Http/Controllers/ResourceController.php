<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Lesson;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    /**
     * LISTAR RECURSOS DE UMA LIÇÃO
     * GET /api/lessons/{lesson}/resources
     */
    public function index(Request $request, Lesson $lesson)
    {
        $user = $request->user();

        // Admin vê tudo
        if ($user->role->name === 'admin') {
            return response()->json(
                $lesson->resources()->get()
            );
        }

        // User só vê se estiver inscrito no curso
        $isEnrolled = $lesson->module
            ->course
            ->enrollments()
            ->where('user_id', $user->id)
            ->exists();

        if ($isEnrolled) {
            return response()->json(
                $lesson->resources()->get()
            );
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * MOSTRAR UM RECURSO ESPECÍFICO
     * GET /api/resources/{resource}
     */
    public function show(Request $request, Resource $resource)
    {
        $user = $request->user();

        // Admin vê tudo
        if ($user->role->name === 'admin') {
            return response()->json($resource->load('lessons'));
        }

        // Verificar se o user tem acesso a pelo menos uma das lições deste resource
        foreach ($resource->lessons as $lesson) {
            $isEnrolled = $lesson->module
                ->course
                ->enrollments()
                ->where('user_id', $user->id)
                ->exists();

            if ($isEnrolled) {
                return response()->json($resource->load('lessons'));
            }
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * CRIAR UM NOVO RECURSO (apenas admin)
     * POST /api/resources
     */
    public function store(Request $request)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'title' => 'required|string',
            'type' => 'required|in:pdf,image,presentation,archive,link,other',
            'size' => 'nullable|string|max:50',
            'url' => 'required|string',
            'lesson_ids' => 'required|array',
            'lesson_ids.*' => 'exists:lessons,id'
        ]);

        // Criar o resource
        $resource = Resource::create([
            'title' => $data['title'],
            'type' => $data['type'],
            'size' => $data['size'] ?? null,
            'url' => $data['url']
        ]);

        // Associar às lições (many-to-many)
        $resource->lessons()->attach($data['lesson_ids']);

        return response()->json($resource->load('lessons'), 201);
    }

    /**
     * ATUALIZAR UM RECURSO (apenas admin)
     * PUT /api/resources/{resource}
     */
    public function update(Request $request, Resource $resource)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'title' => 'sometimes|string',
            'type' => 'sometimes|in:pdf,image,presentation,archive,link,other',
            'size' => 'nullable|string|max:50',
            'url' => 'sometimes|string',
            'lesson_ids' => 'sometimes|array',
            'lesson_ids.*' => 'exists:lessons,id'
        ]);

        // Atualizar dados do resource
        $resource->update([
            'title' => $data['title'] ?? $resource->title,
            'type' => $data['type'] ?? $resource->type,
            'size' => $data['size'] ?? null,
            'url' => $data['url'] ?? $resource->url
        ]);

        // Sincronizar lições se veio lesson_ids
        if (isset($data['lesson_ids'])) {
            $resource->lessons()->sync($data['lesson_ids']);
        }

        return response()->json($resource->load('lessons'));
    }

    /**
     * APAGAR UM RECURSO (apenas admin)
     * DELETE /api/resources/{resource}
     */
    public function destroy(Request $request, Resource $resource)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Apagar relações na tabela pivot
        $resource->lessons()->detach();
        
        // Apagar o resource
        $resource->delete();

        return response()->json(['message' => 'Resource deleted successfully']);
    }
}