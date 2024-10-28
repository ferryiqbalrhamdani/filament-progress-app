<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProjectResource;
use App\Models\Instansi;
use App\Models\Project;
use Illuminate\Contracts\Pagination\CursorPaginator;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Project Baru'),
        ];
    }

    public function getTabs(): array
    {
        $data = [];

        // Tambahkan tab untuk semua data
        $data['all'] = Tab::make('All Data')
            ->modifyQueryUsing(fn(Builder $query) => $query)
            ->badge(fn() => Project::count());

        $instansis = Instansi::get();
        foreach ($instansis as $instansi) {
            $data[$instansi->name] = Tab::make($instansi->name)
                ->modifyQueryUsing(fn(Builder $query) => $query->where('instansi_id', $instansi->id))
                ->badge(fn() => Project::where('instansi_id', $instansi->id)->count());
        }

        return $data;
    }

    protected function getHeaderWidgets(): array
    {
        return ProjectResource::getWidgets();
    }
}
