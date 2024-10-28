<?php

namespace App\Filament\Resources\KontrakResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\KontrakResource;

class EditKontrak extends EditRecord
{
    protected static string $resource = KontrakResource::class;

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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record->user_input_by == null) {
            $this->record->update([
                'user_input_by' => Auth::user()->id,
            ]);
        }

        $dataKontrak = $this->record->kontrakProject->toArray();

        // Hitung progres untuk kontrak dengan bobot 20
        $this->hitungProgresTask($dataKontrak, 20);

        // Update total progres proyek berdasarkan semua task
        $this->updateTotalProgresProject();
    }

    // Fungsi untuk menghitung progres dari task biasa
    protected function hitungProgresTask(array $data, int $bobot): int
    {
        $jumlahTotal = count($data);
        if ($jumlahTotal === 0) {
            return 0;
        }

        $jumlahSelesai = count(array_filter($data, function ($item) {
            return $item['status'] === true;
        }));

        $progres = floor(($jumlahSelesai / $jumlahTotal) * 100);

        $bobotRecord = floor(($progres / 100) * $bobot);

        return $this->record->update([
            'progres' => $progres,
            'bobot' => $bobotRecord,
        ]);
    }

    // Fungsi untuk memperbarui progres total proyek
    protected function updateTotalProgresProject(): void
    {
        $progres = $this->record->bobot + $this->record->project->marcendiser->bobot + $this->record->project->penagihan->bobot + $this->record->project->pengiriman->bobot;

        $this->record->project->update([
            'progres' => $progres,
        ]);
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
