<?php

namespace App\Filament\Resources\PenagihanResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PenagihanResource;
use Illuminate\Support\Js;

class EditPenagihan extends EditRecord
{
    protected static string $resource = PenagihanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('kembali')
                ->label("kembali")
                ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = ' . Js::from($this->previousUrl ?? static::getResource()::getUrl()) . ')')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
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
        $dataBast = $this->record->penagihanBast->toArray();

        // Hitung progres untuk penagihan dengan bobot 50
        $this->hitungProgresTask($dataBast, $dataPenagihan, 10);

        // Update total progres proyek berdasarkan semua task
        $this->updateTotalProgresProject();
    }

    // Fungsi untuk menghitung progres dari task biasa
    protected function hitungProgresTask(array $bast, array $data, int $bobot): int
    {
        $jumlahTotal = count($data);
        if ($jumlahTotal === 0) {
            return 0;
        }

        $filteredDataBast = array_map(function ($item) {
            return [
                'no_bast' => $item['no_bast'] ?? null,
                'tanggal_bast' => $item['tanggal_bast'] ?? null,
            ];
        }, $bast);

        $jumlahNoBast = count(array_filter($filteredDataBast, fn($item) => $item['no_bast'] !== null));
        $jumlahTanggalBast = count(array_filter($filteredDataBast, fn($item) => $item['tanggal_bast'] !== null));

        $jumlahSelesai = count(array_filter($data, function ($item) {
            return $item['status'] === true;
        }));

        $progres = floor((($jumlahSelesai + $jumlahNoBast + $jumlahTanggalBast) / ($jumlahTotal + 2)) * 100);
        if ($progres > 100) {
            $progres = 100.0;
        }

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
