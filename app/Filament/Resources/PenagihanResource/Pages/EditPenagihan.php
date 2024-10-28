<?php

namespace App\Filament\Resources\PenagihanResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PenagihanResource;

class EditPenagihan extends EditRecord
{
    protected static string $resource = PenagihanResource::class;

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

    protected function afterSave(): void
    {
        if ($this->record->user_input_by == null) {
            $this->record->update([
                'user_input_by' => Auth::user()->id,
            ]);
        }

        $dataPenagihan = $this->record->penagihanProject->toArray();

        // Hitung progres untuk penagihan dengan bobot 50
        $this->hitungProgresTask($dataPenagihan, 10);

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
    protected function updateTotalProgresProject()
    {
        $progres = $this->record->bobot + $this->record->project->marcendiser->bobot + $this->record->project->kontrak->bobot + $this->record->project->pengiriman->bobot;

        $this->record->project->update([
            'progres' => $progres,
        ]);
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
