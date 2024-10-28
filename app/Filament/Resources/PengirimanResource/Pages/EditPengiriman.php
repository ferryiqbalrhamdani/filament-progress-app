<?php

namespace App\Filament\Resources\PengirimanResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PengirimanResource;

class EditPengiriman extends EditRecord
{
    protected static string $resource = PengirimanResource::class;

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

        $data = $this->record->pengirimanProject->toArray();
        $dataBaAnname = $this->record->pengirimanBaAnname->toArray();
        $dataBaInname = $this->record->pengirimanBaInname->toArray();
        $dataBast = $this->record->pengirimanBast->toArray();

        $filteredData = array_map(function ($item) {
            return [
                'jenis_pengiriman' => $item['jenis_pengiriman'] ?? null,
                'tanggal_pengiriman' => $item['tanggal_pengiriman'] ?? null,
            ];
        }, $data);

        $filteredDataBaAnname = array_map(function ($item) {
            return [
                'no_ba_anname' => $item['no_ba_anname'] ?? null,
                'tanggal_ba_anname' => $item['tanggal_ba_anname'] ?? null,
            ];
        }, $dataBaAnname);

        $filteredDataBaInname = array_map(function ($item) {
            return [
                'no_ba_inname' => $item['no_ba_inname'] ?? null,
                'tanggal_ba_inname' => $item['tanggal_ba_inname'] ?? null,
            ];
        }, $dataBaInname);

        $filteredDataBast = array_map(function ($item) {
            return [
                'no_bast' => $item['no_bast'] ?? null,
                'tanggal_bast' => $item['tanggal_bast'] ?? null,
            ];
        }, $dataBast);

        // Count non-null fields
        $jumlahJenisPengiriman = count(array_filter($filteredData, fn($item) => $item['jenis_pengiriman'] !== null));
        $jumlahTanggalPengiriman = count(array_filter($filteredData, fn($item) => $item['tanggal_pengiriman'] !== null));
        $jumlahNoBaAnname = count(array_filter($filteredDataBaAnname, fn($item) => $item['no_ba_anname'] !== null));
        $jumlahTanggalBaAnname = count(array_filter($filteredDataBaAnname, fn($item) => $item['tanggal_ba_anname'] !== null));
        $jumlahNoBaInname = count(array_filter($filteredDataBaInname, fn($item) => $item['no_ba_inname'] !== null));
        $jumlahTanggalBaInname = count(array_filter($filteredDataBaInname, fn($item) => $item['tanggal_ba_inname'] !== null));
        $jumlahNoBast = count(array_filter($filteredDataBast, fn($item) => $item['no_bast'] !== null));
        $jumlahTanggalBast = count(array_filter($filteredDataBast, fn($item) => $item['tanggal_bast'] !== null));

        // Calculate total possible fields
        $totalFields = $jumlahNoBaAnname + $jumlahTanggalBaAnname +
            $jumlahNoBaInname + $jumlahTanggalBaInname + 4;

        // Calculate the completion percentage
        $progress = round(((
            $jumlahJenisPengiriman + $jumlahTanggalPengiriman +
            $jumlahNoBaAnname + $jumlahTanggalBaAnname +
            $jumlahNoBaInname + $jumlahTanggalBaInname +
            $jumlahNoBast + $jumlahTanggalBast
        ) / $totalFields) * 100);

        $bobot = 20;
        $bobotRecord = floor(($progress / 100) * $bobot);

        // Update the progress field in the record
        $this->record->update([
            'progres' => $progress,
            'bobot' => $bobotRecord,
        ]);

        $progres = $this->record->bobot + $this->record->project->marcendiser->bobot + $this->record->project->kontrak->bobot + $this->record->project->penagihan->bobot;

        $this->record->project->update([
            'progres' => $progres,
        ]);

        // Dump the filtered data for debugging
        // dd($progress, $totalFields, $jumlahJenisPengiriman, $jumlahTanggalPengiriman, $jumlahNoBaAnname, $jumlahTanggalBaAnname, $jumlahNoBaInname, $jumlahTanggalBaInname, $jumlahNoBast, $jumlahTanggalBast);
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
