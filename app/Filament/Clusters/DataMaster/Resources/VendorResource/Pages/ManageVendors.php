<?php

namespace App\Filament\Clusters\DataMaster\Resources\VendorResource\Pages;

use App\Filament\Clusters\DataMaster\Resources\VendorResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageVendors extends ManageRecords
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
