<?php

namespace App\Filament\Clusters\DataMaster\Resources\JenisLelangResource\Pages;

use App\Filament\Clusters\DataMaster\Resources\JenisLelangResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJenisLelangs extends ManageRecords
{
    protected static string $resource = JenisLelangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
