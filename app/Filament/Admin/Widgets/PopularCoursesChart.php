<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Course;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PopularCoursesChart extends ChartWidget
{
    protected ?string $heading = 'Most Popular Courses';
    
    protected static ?int $sort = 2; // Following your new requested order

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        // Fetching top 5 courses based on enrollment count
        $popularCourses = Course::withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->take(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Students',
                    'data' => $popularCourses->pluck('enrollments_count')->toArray(),
                    'backgroundColor' => [
                        '#7f77dd', // Main Purple
                        '#6366f1', 
                        '#818cf8',
                        '#a5b4fc',
                        '#c7d2fe',
                    ],
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $popularCourses->pluck('title')->map(fn($t) => str($t)->limit(15))->toArray(),
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
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(255, 255, 255, 0.1)', // Those guide lines you liked
                    ],
                    'ticks' => [
                        'precision' => 0,
                        'stepSize' => 1, // Line for every single integer
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}