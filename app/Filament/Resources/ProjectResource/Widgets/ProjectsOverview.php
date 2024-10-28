<?php

namespace App\Filament\Resources\ProjectResource\Widgets;

use Carbon\Carbon;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ProjectsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $countProject = $this->getMonthlyCounts();
        $countComplateProject = $this->getComplateMonthlyCounts();
        $countInComplateProject = $this->getInComplateMonthlyCounts();


        $projectCount = Project::count();
        $projectSelesaiCount = Project::where('status', true)->count();
        $projectBelumSelesaiCount = $projectCount - $projectSelesaiCount;


        return [
            Stat::make('Project', $projectCount)
                ->color($projectCount > 0 ? 'success' : 'gray')
                ->chart($countProject), // Pass the calculated monthly counts for the chart,
            Stat::make('Project Selesai', $projectSelesaiCount)
                ->color($projectSelesaiCount > 0 ? 'success' : 'gray')
                ->chart($countComplateProject),
            Stat::make('Project Belum Selesai', $projectBelumSelesaiCount)
                ->color($projectBelumSelesaiCount > 0 ? 'danger' : 'success')
                ->chart($countInComplateProject),
        ];
    }

    protected function getMonthlyCounts()
    {
        // Get the last 12 months and initialize an array to store the monthly counts
        $months = collect(range(0, 11))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        })->reverse();

        // Query the database directly to get the counts grouped by month
        $counts = Project::selectRaw('TO_CHAR(created_at, \'YYYY-MM\') as month, COUNT(*) as count')
            ->whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Return the counts for each month, or 0 if no records exist for a given month
        return $months->map(function ($month) use ($counts) {
            return $counts[$month] ?? 0;
        })->toArray();
    }
    protected function getComplateMonthlyCounts()
    {
        // Get the last 12 months and initialize an array to store the monthly counts
        $months = collect(range(0, 11))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        })->reverse();

        // Query the database directly to get the counts grouped by month
        $counts = Project::selectRaw('TO_CHAR(created_at, \'YYYY-MM\') as month, COUNT(*) as count')
            ->where('status', true)
            ->whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Return the counts for each month, or 0 if no records exist for a given month
        return $months->map(function ($month) use ($counts) {
            return $counts[$month] ?? 0;
        })->toArray();
    }
    protected function getInComplateMonthlyCounts()
    {
        // Get the last 12 months and initialize an array to store the monthly counts
        $months = collect(range(0, 11))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        })->reverse();

        // Query the database directly to get the counts grouped by month
        $counts = Project::selectRaw('TO_CHAR(created_at, \'YYYY-MM\') as month, COUNT(*) as count')
            ->where('status', false)
            ->whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Return the counts for each month, or 0 if no records exist for a given month
        return $months->map(function ($month) use ($counts) {
            return $counts[$month] ?? 0;
        })->toArray();
    }
}
