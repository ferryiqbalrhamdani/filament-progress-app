<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisAnggaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tb_jenis_anggaran';

    protected $fillable = ['name'];
}
