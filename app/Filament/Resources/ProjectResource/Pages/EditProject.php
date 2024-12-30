<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Js;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('Kembali')
                ->label("Kembali")
                ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = ' . Js::from($this->previousUrl ?? static::getResource()::getUrl()) . ')')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['bebas_pajak'] == false) {
            $data['bebas_pajak_khusus'] = null;
        }
        if ($data['asal_brand_id'] == 1) {
            $data['asal_brand_khusus'] = null;
        }
        return $data;
    }


    protected function afterSave(): void
    {
        // dd($this->data['dp_total']);

        if ($this->data['dp_total']) {
            $this->record->projectDp()->updateOrCreate([
                'dp_total' => $this->data['dp_total'],
            ]);
        }

        if ($this->record->garansi == false) {
            // If the garansi is false, delete the related lamaGaransi record if it exists
            $this->record->lamaGaransi()->delete();
        }

        if ($this->record->payment_term != 'DP') {
            // Check if the related projectDp record exists
            if ($this->record->projectDp) {
                // If it exists, update it
                $this->record->projectDp()->delete();
            }
        }

        if ($this->record->payment_term != 'Termin') {
            if ($this->record->termins) {
                $this->record->termins()->delete();
            }
        }

        if ($this->record->payment_term == 'Tidak ada DP') {
            if ($this->record->projectDp) {
                $this->record->projectDp()->delete();
            }

            if ($this->record->termins) {
                $this->record->termins()->delete();
            }
        }
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
