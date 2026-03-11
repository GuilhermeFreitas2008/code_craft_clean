<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'lesson_id',
        'parent_id',
        'content',
        'likes'
    ];

    // Relacionamentos
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    // Para respostas (auto-relacionamento) - COM LIMITE
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
                    ->with(['user', 'replies' => function($query) {
                        $query->limit(5); // Limita a profundidade
                    }])
                    ->limit(10); // Limita número de respostas
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Relacionamento com likes
    public function likesRelation()
    {
        return $this->hasMany(CommentLike::class);
    }

    // Users que deram like (many-to-many)
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'comment_likes')
                    ->withTimestamps();
    }

    // Verificar se um user deu like
    public function isLikedBy(User $user)
    {
        return $this->likesRelation()->where('user_id', $user->id)->exists();
    }
}