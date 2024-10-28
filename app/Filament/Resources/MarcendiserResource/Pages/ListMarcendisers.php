<?php

namespace App\Filament\Resources\MarcendiserResource\Pages;

use App\Filament\Resources\MarcendiserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarcendisers extends ListRecords
{
    protected static string $resource = MarcendiserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
