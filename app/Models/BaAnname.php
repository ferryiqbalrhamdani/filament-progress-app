<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaAnname extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_ba_anname',
        'tanggal_ba_anname',
    ];
}
