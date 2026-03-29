<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Category;
use Filament\Widgets\ChartWidget;

class CoursesByCategoryChart extends ChartWidget
{
    protected ?string $heading = 'Courses by Category';
    
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        // Fetching top 6 categories that actually have courses
        $categoriesWithCounts = Category::withCount('courses')
            ->has('courses')
            ->orderByDesc('courses_count')
            ->take(6)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Courses',
                    'data' => $categoriesWithCounts->pluck('courses_count')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', // Blue
                        '#10b981', // Green
                        '#f59e0b', // Amber
                        '#ef4444', // Red
                        '#8b5cf6', // Violet
                        '#ec4899'  // Pink
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $categoriesWithCounts->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => true,
            'aspectRatio' => 1.3,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}