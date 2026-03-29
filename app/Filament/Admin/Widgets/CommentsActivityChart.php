<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Comment;
use App\Models\Course;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CommentsActivityChart extends ChartWidget
{
    protected ?string $heading = 'Comment Activity';
    
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = 1;

    public ?string $filter = null;

    public function mount(): void
    {
        // Set default course as the one with the most comments
        $topCourseId = DB::table('courses')
            ->join('modules', 'courses.id', '=', 'modules.course_id')
            ->join('lessons', 'modules.id', '=', 'lessons.module_id')
            ->join('comments', 'lessons.id', '=', 'comments.lesson_id')
            ->select('courses.id')
            ->groupBy('courses.id')
            ->orderByRaw('COUNT(comments.id) DESC')
            ->limit(1)
            ->value('id');

        $this->filter = $topCourseId ? (string) $topCourseId : null;
    }

    protected function getFilters(): ?array
    {
        // Simple list for the native select (ordered by title)
        return Course::orderBy('title')->pluck('title', 'id')->toArray();
    }

    protected function getData(): array
    {
        $year = now()->year;
        $query = Comment::query();

        if ($this->filter) {
            $query->whereHas('lesson.module.course', function ($q) {
                $q->where('courses.id', $this->filter);
            });
        }

        $data = $query->selectRaw('EXTRACT(MONTH FROM created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $counts = [];
        foreach (range(1, 12) as $m) {
            $counts[] = $data[$m] ?? $data[(string)$m] ?? $data[(float)$m] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => "Comments",
                    'data' => $counts,
                    'backgroundColor' => '#10b981',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => true,
            'aspectRatio' => 1.3,
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true, 
                    'ticks' => ['precision' => 0]
                ],
                'x' => [
                    'grid' => ['display' => false],
                ],
            ],
        ];
    }
}