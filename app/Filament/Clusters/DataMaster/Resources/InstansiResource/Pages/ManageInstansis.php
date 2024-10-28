<?php

namespace App\Filament\Clusters\DataMaster\Resources\InstansiResource\Pages;

use App\Filament\Clusters\DataMaster\Resources\InstansiResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageInstansis extends ManageRecords
{
    protected static string $resource = InstansiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
