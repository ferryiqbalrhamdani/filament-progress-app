<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenagihanProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'penagihan_id',
        'name',
        'status',
        'jenis_penagihan',
    ];
}
