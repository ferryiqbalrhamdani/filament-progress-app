<?php

namespace App\Filament\Resources\MarcendiserResource\Pages;

use App\Filament\Resources\MarcendiserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarcendiser extends EditRecord
{
    protected static string $resource = MarcendiserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('Kembali')
                ->label('Kembali') // Set label for the button
                ->url($this->getResource()::getUrl('index')) // Redirect to the index page of the resource
                ->icon('heroicon-o-arrow-left') // Optionally set an icon,
                ->outlined()
        ];
    }
}
