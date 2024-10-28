<?php

namespace App\Filament\Clusters\DataMaster\Resources\JenisAnggaranResource\Pages;

use App\Filament\Clusters\DataMaster\Resources\JenisAnggaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJenisAnggarans extends ManageRecords
{
    protected static string $resource = JenisAnggaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
