<?php

namespace App\Filament\Admin\Widgets;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    // Opcional: Define a ordem de aparecimento no dashboard
    protected static ?int $sort = 1; 

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Users and Admins')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
                
            Stat::make('Total Courses', Course::count())
                ->description('Courses created')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),
                
            Stat::make('Total Lessons', Lesson::count())
                ->description('Lessons available')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),
        ];
    }
}