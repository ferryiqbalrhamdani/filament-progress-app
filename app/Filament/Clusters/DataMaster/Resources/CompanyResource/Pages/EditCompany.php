<?php

namespace App\Filament\Clusters\DataMaster\Resources\CompanyResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Clusters\DataMaster\Resources\CompanyResource;
use App\Models\Company;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    public function findRecord($key): Model
    {
        return Company::where('slug', $key)->firstOrFail();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
