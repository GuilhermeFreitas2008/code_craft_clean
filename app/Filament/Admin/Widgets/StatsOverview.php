<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment; // Importamos o modelo de inscrições
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    // Definimos como 0 ou 1 para aparecer no topo de tudo
    protected static ?int $sort = 0; 

    protected function getStats(): array
    {
        return [
            Stat::make('Total of Users', User::count())
                ->description('Users & Admins')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
                
            Stat::make('Total of Courses', Course::count())
                ->description('Public Courses')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),
                
            Stat::make('Total of Enrollments', Enrollment::count())
                ->description('enrolled users')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('warning'), // Cor de destaque para vendas/conversão
        ];
    }
}