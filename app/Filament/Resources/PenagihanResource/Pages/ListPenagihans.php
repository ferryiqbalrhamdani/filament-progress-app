<?php

namespace App\Filament\Resources\PenagihanResource\Pages;

use Filament\Actions;
use App\Models\Penagihan;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\PenagihanResource;

class ListPenagihans extends ListRecords
{
    protected static string $resource = PenagihanResource::class;

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
                ->badge(fn() => Penagihan::count()),
            'my_data' => Tab::make('Data Saya')
                ->query(
                    fn($query) => $query->where('user_input_by', Auth::user()->id)
                )
                ->badge(fn() => Penagihan::where('user_input_by', Auth::user()->id)->count()),
        ];
    }
}
