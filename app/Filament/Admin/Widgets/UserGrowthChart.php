<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UserGrowthChart extends ChartWidget
{
    protected ?string $heading = 'User Growth';
    
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 1; 

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

        // Optimized for PostgreSQL/Supabase
        $data = User::selectRaw('EXTRACT(MONTH FROM created_at) as month, COUNT(*) as count')
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
                    'label' => "New Users",
                    'data' => $counts,
                    'fill' => 'start',
                    'tension' => 0.4,
                    'backgroundColor' => 'rgba(127, 119, 221, 0.1)',
                    'borderColor' => '#7f77dd',
                    'pointBackgroundColor' => '#7f77dd',
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
            'maintainAspectRatio' => true,
            'aspectRatio' => 1.3,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
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
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}