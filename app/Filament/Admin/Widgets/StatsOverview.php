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
            Stat::make('Total de Utilizadores', User::count())
                ->description('Alunos e Admins')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
                
            Stat::make('Cursos na Plataforma', Course::count())
                ->description('Cursos criados')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),
                
            Stat::make('Total de Lições', Lesson::count())
                ->description('Aulas disponíveis')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),
        ];
    }
}