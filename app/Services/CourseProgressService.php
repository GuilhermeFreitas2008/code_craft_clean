<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use App\Models\UserCourseProgress;

class CourseProgressService
{
    // Mudei o retorno de 'void' para 'UserCourseProgress'
    public static function update(User $user, Course $course): UserCourseProgress
    {
        // total de lições do curso
        $totalLessons = $course->modules()
            ->withCount('lessons')
            ->get()
            ->sum('lessons_count');

        if ($totalLessons === 0) {
            // Se o curso não tem lições, definimos 0% para evitar divisão por zero
            // e retornamos o registo
            return UserCourseProgress::updateOrCreate(
                ['user_id' => $user->id, 'course_id' => $course->id],
                ['progress_percent' => 0, 'last_updated' => now()]
            );
        }

        // lições concluídas pelo user neste curso
        $completedLessons = $user->lessonProgress()
            ->whereHas('lesson.module', function ($q) use ($course) {
                $q->where('course_id', $course->id);
            })
            ->where('completed', true)
            ->count();

        $percentage = round(($completedLessons / $totalLessons) * 100, 2);

        // Adicionei 'return' aqui
        return UserCourseProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'progress_percent' => $percentage,
                'last_updated' => now(),
            ]
        );
    }
}