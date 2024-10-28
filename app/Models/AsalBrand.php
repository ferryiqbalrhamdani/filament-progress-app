<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsalBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function sertifikatProduks()
    {
        return $this->hasMany(SertifikatProduk::class);
    }
}
