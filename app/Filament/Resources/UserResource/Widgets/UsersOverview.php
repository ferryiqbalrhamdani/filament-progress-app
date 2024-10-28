<?php

namespace App\Filament\Resources\UserResource\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\UserResource\Pages\ListUsers;

class UsersOverview extends BaseWidget
{

    use InteractsWithPageTable;


    protected function getTablePage(): string
    {
        return ListUsers::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total User', number_format($this->getPageTableQuery()->count(), 0, ',', '.')),
            Stat::make('Total User Aktif', number_format($this->getPageTableQuery()->where('status', true)->count(), 0, ',', '.')),
            Stat::make('Total User Tidak Aktif', number_format($this->getPageTableQuery()->where('status', false)->count(), 0, ',', '.')),
        ];
    }
}
