<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Po extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'no_po',
        'jumlah_item',
        'jumlah_ea',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
