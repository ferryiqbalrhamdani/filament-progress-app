<?php

namespace App\Filament\Resources\KontrakResource\Pages;

use Filament\Actions;
use App\Models\Kontrak;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\KontrakResource;

class ListKontraks extends ListRecords
{
    protected static string $resource = KontrakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('Semua Data')
                ->badge(fn() => Kontrak::count()),
            'my_data' => Tab::make('Data Saya')
                ->query(
                    fn($query) => $query->where('user_input_by', Auth::user()->id)
                )
                ->badge(fn() => Kontrak::where('user_input_by', Auth::user()->id)->count()),
        ];
    }
}
