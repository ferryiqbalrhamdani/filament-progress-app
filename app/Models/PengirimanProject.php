<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengirimanProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengiriman_id',
        'jenis_pengiriman',
        'tanggal_pengiriman',
    ];

    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class);
    }
}
