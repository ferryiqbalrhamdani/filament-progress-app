<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SertifikatProduk extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function asalBrand()
    {
        return $this->belongsTo(AsalBrand::class);
    }
}
