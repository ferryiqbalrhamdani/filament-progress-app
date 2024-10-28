<?php

namespace Database\Seeders;

use App\Models\AsalBrand;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AsalBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AsalBrand::create(['name' => 'Lokal']);
        AsalBrand::create(['name' => 'Import']);
    }
}
