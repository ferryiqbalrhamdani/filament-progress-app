<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisLelang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tb_jenis_lelang';

    protected $fillable = ['name'];
}
