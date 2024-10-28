<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class NilaiKontrakOverview extends BaseWidget
{
    use HasWidgetShield;
    protected function getStats(): array
    {
        // Calculate the total contract value for all projects
        $totalNilaiKontrak = Project::sum('nilai_kontrak');
        $completedNilaiKontrak = Project::where('status', true)->sum('nilai_kontrak');
        $inCompletedNilaiKontrak = $totalNilaiKontrak - $completedNilaiKontrak;

        // Optionally, calculate the contract value for completed projects
        // Assuming there's a 'status' field to indicate completion
        // $completedNilaiKontrak = Project::where('status', 'completed')->sum('nilai_kontrak');

        // Calculate the monthly contract values for the last 12 months
        $monthlyContractValues = $this->getMonthlyContractValues();
        $monthlyCompletedContractValues = $this->getMonthlyCompletedContractValues();
        $monthlyInCompletedContractValues = $this->getMonthlyIncompleteContractValues();

        return [
            Stat::make('Nilai Kontrak', 'Rp ' . number_format($totalNilaiKontrak, 0, ',', '.'))
                ->color($totalNilaiKontrak > 0 ? 'success' : 'gray')
                ->extraAttributes([
                    'class' => 'text-sm', // Membuat ukuran tulisan lebih kecil
                ])
                ->chart($monthlyContractValues), // Display the monthly contract values in the chart
            Stat::make('Nilai Kontrak Selesai', 'Rp ' . number_format($completedNilaiKontrak, 0, ',', '.'))
                ->color($completedNilaiKontrak > 0 ? 'success' : 'gray')
                ->extraAttributes([
                    'class' => 'text-sm', // Membuat ukuran tulisan lebih kecil
                ])
                ->chart($monthlyCompletedContractValues),
            Stat::make('Nilai Kontrak Belum Selesai', 'Rp ' . number_format($inCompletedNilaiKontrak, 0, ',', '.'))
                ->color($inCompletedNilaiKontrak > 0 ? 'danger' : 'gray')
                ->extraAttributes([
                    'class' => 'text-sm', // Membuat ukuran tulisan lebih kecil
                ])
                ->chart($monthlyInCompletedContractValues),
        ];
    }

    protected function getMonthlyContractValues(): array
    {
        // Get the last 12 months
        $months = collect(range(0, 11))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        })->reverse();

        // Query the total contract values grouped by month
        $monthlyValues = Project::selectRaw('TO_CHAR(created_at, \'YYYY-MM\') as month, SUM(nilai_kontrak) as total')
            ->whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Return the total contract values for each month, or 0 if no records exist
        return $months->map(function ($month) use ($monthlyValues) {
            return $monthlyValues[$month] ?? 0;
        })->toArray();
    }

    protected function getMonthlyCompletedContractValues(): array
    {
        // Get the last 12 months
        $months = collect(range(0, 11))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        })->reverse();

        // Query the total contract values for completed projects grouped by month
        $monthlyCompletedValues = Project::selectRaw('TO_CHAR(created_at, \'YYYY-MM\') as month, SUM(nilai_kontrak) as total')
            ->where('status', true) // Filter for completed projects
            ->whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Return the total contract values for each month, or 0 if no records exist
        return $months->map(function ($month) use ($monthlyCompletedValues) {
            return $monthlyCompletedValues[$month] ?? 0;
        })->toArray();
    }

    protected function getMonthlyIncompleteContractValues(): array
    {
        // Get the last 12 months
        $months = collect(range(0, 11))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        })->reverse();

        // Query the total contract values for incomplete projects grouped by month
        $monthlyIncompleteValues = Project::selectRaw('TO_CHAR(created_at, \'YYYY-MM\') as month, SUM(nilai_kontrak) as total')
            ->where('status', false) // Filter for incomplete projects
            ->whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Return the total contract values for each month, or 0 if no records exist
        return $months->map(function ($month) use ($monthlyIncompleteValues) {
            return $monthlyIncompleteValues[$month] ?? 0;
        })->toArray();
    }
}
