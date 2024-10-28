<?php

namespace App\Filament\Resources\PengirimanResource\Pages;

use Filament\Actions;
use App\Models\Pengiriman;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\PengirimanResource;

class ListPengirimen extends ListRecords
{
    protected static string $resource = PengirimanResource::class;

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
                ->badge(fn() => Pengiriman::count()),
            'my_data' => Tab::make('Data Saya')
                ->query(
                    fn($query) => $query->where('user_input_by', Auth::user()->id)
                )
                ->badge(fn() => Pengiriman::where('user_input_by', Auth::user()->id)->count()),
        ];
    }
}
