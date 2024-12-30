<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Js;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('kembali')
                ->label("kembali")
                ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = ' . Js::from($this->previousUrl ?? static::getResource()::getUrl()) . ')')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
        ];
    }
}
