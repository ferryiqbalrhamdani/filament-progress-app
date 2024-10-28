<?php

namespace App\Filament\Clusters\DataMaster\Resources\SertifikatProdukResource\Pages;

use App\Filament\Clusters\DataMaster\Resources\SertifikatProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSertifikatProduks extends ManageRecords
{
    protected static string $resource = SertifikatProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
