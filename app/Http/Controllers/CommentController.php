<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Lesson;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * LISTAR COMENTÁRIOS DE UMA LIÇÃO
     * GET /api/lessons/{lesson}/comments
     */
    public function index(Request $request, Lesson $lesson)
    {
        $user = $request->user();

        // Verificar permissão (admin ou inscrito)
        if ($user->role->name !== 'admin') {
            $isEnrolled = $lesson->module
                ->course
                ->enrollments()
                ->where('user_id', $user->id)
                ->exists();

            if (!$isEnrolled) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        }

        // Buscar comentários com informações de likes
        $comments = $lesson->comments()
            ->whereNull('parent_id')
            ->with([
                'user', 
                'replies.user',
                'likedByUsers' => function($query) use ($user) {
                    $query->where('user_id', $user->id);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Mapear para adicionar o campo is_liked_by_user
        $comments->each(function ($comment) use ($user) {
            $comment->is_liked_by_user = $comment->likedByUsers->isNotEmpty();

            $comment->userAvatar = $comment->user->avatar ?? null;
            
            if ($comment->replies) {
                $comment->replies->each(function ($reply) {
                    $reply->is_liked_by_user = $reply->likedByUsers->isNotEmpty();
                    $reply->userAvatar = $reply->user->avatar ?? null;
                });
            }
            
            // Remover a relação carregada para não poluir o JSON
            unset($comment->likedByUsers);
        });

        return response()->json($comments);
    }

    /**
     * CRIAR COMENTÁRIO
     * POST /api/lessons/{lesson}/comments
     */
    public function store(Request $request, Lesson $lesson)
    {
        $user = $request->user();

        // Verificar se está inscrito no curso
        $isEnrolled = $lesson->module
            ->course
            ->enrollments()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isEnrolled && $user->role->name !== 'admin') {
            return response()->json(['error' => 'You must be enrolled to comment'], 403);
        }

        $data = $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $comment = $lesson->comments()->create([
            'user_id' => $user->id,
            'content' => $data['content'],
            'parent_id' => $data['parent_id'] ?? null,
            'likes' => 0
        ]);

        $comment->load('user');
        $comment->userAvatar = $user->avatar;

        return response()->json($comment->load('user'), 201);
    }

    /**
     * ATUALIZAR COMENTÁRIO
     * PUT /api/comments/{comment}
     */
    public function update(Request $request, Comment $comment)
    {
        $user = $request->user();

        // Verificar permissão (admin ou dono do comentário)
        if ($user->role->name !== 'admin' && $comment->user_id !== $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'content' => 'required|string'
        ]);

        $comment->update($data);

        return response()->json($comment);
    }

    /**
     * APAGAR COMENTÁRIO
     * DELETE /api/comments/{comment}
     */
    public function destroy(Request $request, Comment $comment)
    {
        $user = $request->user();

        // Verificar permissão (admin ou dono do comentário)
        if ($user->role->name !== 'admin' && $comment->user_id !== $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }

    /**
     * DAR/REMOVER LIKE NUM COMENTÁRIO
     * POST /api/comments/{comment}/like
     */
    public function like(Request $request, Comment $comment)
    {
        $user = $request->user();

        // Verificar se está inscrito no curso da lição
        $isEnrolled = $comment->lesson
            ->module
            ->course
            ->enrollments()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isEnrolled && $user->role->name !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Verificar se já deu like
        $existingLike = $comment->likesRelation()->where('user_id', $user->id)->first();

        if ($existingLike) {
            // Remove like
            $existingLike->delete();
            $message = 'Like removed';
            $liked = false;
            $comment->decrement('likes');
        } else {
            // Adiciona like
            $comment->likesRelation()->create(['user_id' => $user->id]);
            $message = 'Like added';
            $liked = true;
            $comment->increment('likes');
        }

        return response()->json([
            'message' => $message,
            'liked' => $liked,
            'likes' => $comment->fresh()->likes
        ]);
    }
}