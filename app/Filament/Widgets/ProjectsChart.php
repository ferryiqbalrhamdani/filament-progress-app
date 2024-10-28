<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Project;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class ProjectsChart extends ChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Chart Jumlah Project';

    public ?string $filter = 'year'; // Default filter set to 'year'

    protected int | string | array $columnSpan = 'full';

    protected static string $color = 'info';

    protected static ?string $maxHeight = '300px';

    protected static ?int $sort = 7;

    /**
     * Define the filters for the widget.
     */
    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hari Ini',
            'week' => 'Minggu Terakhir',
            'month' => 'Bulan Terakhir',
            'year' => 'Tahun Ini',
            'all' => 'Semua Tahun', // New filter for all years
        ];
    }

    /**
     * Get the chart data based on the selected filter.
     */
    // protected function getData(): array
    // {
    //     // Get the active filter (default is 'year')
    //     $activeFilter = $this->filter;

    //     // Determine the date range based on the active filter
    //     $dateRange = $this->getDateRangeByFilter($activeFilter);

    //     // Fetch the data based on the selected filter
    //     $dataQuery = Trend::model(Project::class)
    //         ->dateColumn('tanggal_kontrak')
    //         ->between(
    //             start: $dateRange['start'],
    //             end: $dateRange['end'],
    //         );

    //     // Query both project count and total project value
    //     if ($activeFilter === 'today') {
    //         $dataCount = $dataQuery->perHour()->count();
    //         $dataValue = $dataQuery->perHour()->sum('nilai_kontrak');
    //     } elseif ($activeFilter === 'year') {
    //         $dataCount = $dataQuery->perMonth()->count();
    //         $dataValue = $dataQuery->perMonth()->sum('nilai_kontrak');
    //     } elseif ($activeFilter === 'all') {
    //         $dataCount = $dataQuery->perYear()->count(); // Aggregate by year for "all" filter
    //         $dataValue = $dataQuery->perYear()->sum('nilai_kontrak'); // Sum project values by year
    //     } else {
    //         $dataCount = $dataQuery->perDay()->count();
    //         $dataValue = $dataQuery->perDay()->sum('nilai_kontrak');
    //     }

    //     // Format the data for the chart
    //     return [
    //         'datasets' => [
    //             [
    //                 'label' => 'Jumlah Project',
    //                 'data' => $dataCount->map(fn(TrendValue $value) => intval($value->aggregate)),
    //                 'fill' => true,
    //                 'tension' => 0.3,
    //                 'borderColor' => '#4e73df', // Example color for project count line
    //             ],
    //             [
    //                 'label' => 'Nilai Kontrak',
    //                 'data' => $dataValue->map(fn(TrendValue $value) => intval($value->aggregate)), // Keep as numbers
    //                 'fill' => true,
    //                 'tension' => 0.3,
    //                 'borderColor' => '#1cc88a', // Example color for project value line
    //                 'yAxisID' => 'B', // Optional: Use second Y axis for better distinction
    //             ],
    //         ],
    //         'labels' => $dataCount->map(fn(TrendValue $value) => $this->formatLabel($value->date, $activeFilter)),
    //     ];
    // }

    protected function getData(): array
    {
        // Get the active filter (default is 'year')
        $activeFilter = $this->filter;

        // Determine the date range based on the active filter
        $dateRange = $this->getDateRangeByFilter($activeFilter);

        // Fetch the data based on the selected filter
        $dataQuery = Trend::model(Project::class)
            ->dateColumn('tanggal_kontrak')
            ->between(
                start: $dateRange['start'],
                end: $dateRange['end'],
            );

        // Query both project count and total project value
        if ($activeFilter === 'today') {
            $dataCount = $dataQuery->perHour()->count();
            $dataValue = $dataQuery->perHour()->sum('nilai_kontrak');
        } elseif ($activeFilter === 'year') {
            $dataCount = $dataQuery->perMonth()->count();
            $dataValue = $dataQuery->perMonth()->sum('nilai_kontrak');
        } elseif ($activeFilter === 'all') {
            $dataCount = $dataQuery->perYear()->count(); // Aggregate by year for "all" filter
            $dataValue = $dataQuery->perYear()->sum('nilai_kontrak'); // Sum project values by year
        } else {
            $dataCount = $dataQuery->perDay()->count();
            $dataValue = $dataQuery->perDay()->sum('nilai_kontrak');
        }

        // Format the data for the chart
        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Project',
                    'data' => $dataCount->map(fn(TrendValue $value) => intval($value->aggregate)),
                    'fill' => true,
                    'tension' => 0.3,
                    'borderColor' => '#4e73df', // Example color for project count line
                ],
                [
                    'label' => 'Nilai Kontrak',
                    'data' => $dataValue->map(fn(TrendValue $value) =>  intval($value->aggregate)),
                    'fill' => true,
                    'tension' => 0.3,
                    'borderColor' => '#1cc88a', // Example color for project value line
                    // 'yAxisID' => 'B',
                ],
            ],
            'labels' => $dataCount->map(fn(TrendValue $value) => $this->formatLabel($value->date, $activeFilter)),
        ];
    }



    /**
     * Helper method to determine the date range based on the filter.
     */
    protected function getDateRangeByFilter(string $filter): array
    {
        switch ($filter) {
            case 'today':
                return [
                    'start' => Carbon::now()->startOfDay(),
                    'end' => Carbon::now()->endOfDay(),
                ];
            case 'week':
                return [
                    'start' => Carbon::now()->startOfWeek(),
                    'end' => Carbon::now()->endOfWeek(),
                ];
            case 'month':
                return [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfMonth(),
                ];
            case 'all':
                $earliestDate = Project::min('tanggal_kontrak');
                return [
                    'start' => $earliestDate ? Carbon::parse($earliestDate) : Carbon::now()->subYears(10),
                    'end' => Carbon::now(),
                ];
            case 'year':
            default:
                return [
                    'start' => Carbon::now()->startOfYear(),
                    'end' => Carbon::now()->endOfYear(),
                ];
        }
    }


    /**
     * Helper method to format the label based on the filter.
     */
    protected function formatLabel(string $date, string $filter): string
    {
        if ($filter === 'year') {
            return Carbon::parse($date)->format('M'); // Format as month for year filter
        } elseif ($filter === 'today') {
            return Carbon::parse($date)->format('H:i'); // Format as hour for today filter
        } elseif ($filter === 'all') {
            return $date; // Format as year for "all" filter
        }

        return Carbon::parse($date)->format('d M'); // Format as day and month for other filters
    }

    /**
     * Define the type of chart.
     */
    protected function getType(): string
    {
        return 'line';
    }

    /**
     * Get the description for the chart widget.
     */
    public function getDescription(): ?string
    {
        return 'Angka Dari Project Berdasarkan ' . $this->getFilters()[$this->filter];
    }
}
