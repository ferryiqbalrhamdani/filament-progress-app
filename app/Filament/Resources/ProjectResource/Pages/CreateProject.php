<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        return $data;
    }


    protected function afterCreate(): void
    {
        // data
        $data = [
            'Usul Pesanan',
            'Sprinada',
            'Prakualifikasi',
            'SPH',
            'No Kontrak',
        ];

        $dataPenagihanUmum = [
            'SIMB',
            'SPPM',
            'Surat Pengantar Barang (PT)',
            'Packing List (PT)',
            'Invoice',
            'Packing List',
            'AWB/BL',
            'Kontrak',
            'Amademen Kontrak',
            'Surat Pernyataan Barang',
        ];

        $dataPenagihanSpesifik = [
            'Surat Permohonan',
            'Kwitansi',
            'BAP',
            'SSP/PPN/PPH',
            'Efaktur',
            'Packing List',
            'AWB/BL',
            'Kontrak',
        ];

        // kontrak
        $kontrak = $this->record->kontrak()->create([
            'project_id' => $this->record->id,
        ]);

        foreach ($data as $value) {
            $kontrak->kontrakProject()->create([
                'name' => $value,
            ]);
        }

        // penagihan
        $penagihan = $this->record->penagihan()->create([
            'project_id' => $this->record->id,
        ]);

        foreach ($dataPenagihanUmum as $value) {
            $penagihan->penagihanProject()->create([
                'name' => $value,
                'jenis_penagihan' => 'Pelunasan',
            ]);
        }

        if ($this->record->payment_term != 'Tidak ada DP') {
            $jenis = '';
            if ($this->record->payment_term == 'DP') {
                $jenis = 'DP';
            } elseif ($this->record->payment_term == 'Termin') {
                $jenis = 'Termin';
            }

            foreach ($dataPenagihanSpesifik as $value) {
                $penagihan->penagihanProject()->create([
                    'name' => $value,
                    'jenis_penagihan' => $jenis,
                ]);
            }
        }

        // pengiriman
        $pengiriman = $this->record->pengiriman()->create([
            'project_id' => $this->record->id,
        ]);

        $pengiriman->pengirimanProject()->create([
            'pengiriman_id' => $pengiriman->id,
        ]);

        // marcendiser
        $this->record->marcendiser()->create([
            'project_id' => $this->record->id,
        ]);


        if ($this->record->garansi == false) {
            // If the garansi is false, delete the related lamaGaransi record if it exists
            $this->record->lamaGaransi()->delete();
        }

        if ($this->record->payment_term == 'DP') {
            // Check if the related projectDp record exists
            if ($this->record->projectDp) {
                // If it exists, update it
                $this->record->projectDp()->update([
                    'dp_total' => $this->data['dp_total'],
                ]);
            } else {
                // If it doesn't exist, create a new record
                $this->record->projectDp()->create([
                    'dp_total' => $this->data['dp_total'],
                ]);
            }
        }
    }
}
