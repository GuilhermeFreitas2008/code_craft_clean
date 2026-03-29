<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Enrollment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EnrollmentChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Enrollments';
    
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 2; 

    // Keeps the box slim as requested
    protected ?string $maxHeight = '200px';

    public ?string $filter = null;

    protected function getFilters(): ?array
    {
        $currentYear = now()->year;
        
        return [
            (string) $currentYear => (string) $currentYear,
            (string) ($currentYear - 1) => (string) ($currentYear - 1),
            (string) ($currentYear - 2) => (string) ($currentYear - 2),
        ];
    }

    protected function getData(): array
    {
        $year = $this->filter ?? now()->year;

        $data = Enrollment::selectRaw('EXTRACT(MONTH FROM created_at) as month, COUNT(*) as count')
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
                    'label' => "New Enrollments",
                    'data' => $counts,
                    'fill' => 'start',
                    'tension' => 0.4,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => '#3b82f6',
                    'pointBackgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(255, 255, 255, 0.1)',
                    ],
                    'ticks' => [
                        'precision' => 0,
                        'stepSize' => 1,
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
            'layout' => [
                'padding' => [
                    'top' => 10,
                    'bottom' => 0,
                ],
            ],
        ];
    }
}