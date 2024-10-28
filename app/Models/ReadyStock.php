<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadyStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'jumlah_item',
        'jumlah_ea',
    ];
}
