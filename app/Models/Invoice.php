<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_id',
        'no_invoice',
    ];

    public function po()
    {
        return $this->belongsTo(Po::class);
    }
}
