<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Kembali')
                ->label('Kembali') // Set label for the button
                ->url($this->getResource()::getUrl('index')) // Redirect to the index page of the resource
                ->icon('heroicon-o-arrow-left') // Optionally set an icon,
                ->outlined()
        ];
    }
}
