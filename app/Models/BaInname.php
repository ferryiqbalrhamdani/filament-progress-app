<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaInname extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_ba_inname',
        'tanggal_ba_inname',
    ];
}
