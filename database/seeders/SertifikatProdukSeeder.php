<?php

namespace Database\Seeders;

use App\Models\AsalBrand;
use Illuminate\Database\Seeder;
use App\Models\SertifikatProduk;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SertifikatProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lokal = AsalBrand::where('name', 'Lokal')->first();
        $import = AsalBrand::where('name', 'Import')->first();

        SertifikatProduk::create(['name' => 'Sertifikat Keaslian Produk', 'asal_brand_id' => $lokal->id]);
        SertifikatProduk::create(['name' => 'Sertifikat Keaslian Produksi', 'asal_brand_id' => $lokal->id]);
        SertifikatProduk::create(['name' => 'TKDN', 'asal_brand_id' => $lokal->id]);
        SertifikatProduk::create(['name' => 'TKDNLITBAG', 'asal_brand_id' => $lokal->id]);

        SertifikatProduk::create(['name' => 'COC', 'asal_brand_id' => $import->id]);
        SertifikatProduk::create(['name' => 'COO', 'asal_brand_id' => $import->id]);
        SertifikatProduk::create(['name' => 'COM', 'asal_brand_id' => $import->id]);
        SertifikatProduk::create(['name' => 'ARC', 'asal_brand_id' => $import->id]);
    }
}
