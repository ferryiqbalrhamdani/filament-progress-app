<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengiriman';

    protected $fillable = [
        'project_id',
        'user_input_by',
        'progres',
        'bobot',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function userInputBy()
    {
        return $this->belongsTo(User::class, 'user_input_by');
    }

    public function pengirimanProject()
    {
        return $this->hasOne(PengirimanProject::class, 'pengiriman_id');
    }
    public function pengirimanBast()
    {
        return $this->hasOne(Bast::class, 'pengiriman_id');
    }

    public function pengirimanBaAnname()
    {
        return $this->belongsToMany(BaAnname::class, 'pengiriman_ba_anname', 'pengiriman_id', 'ba_anname_id');
    }

    public function pengirimanBaInname()
    {
        return $this->belongsToMany(BaInname::class, 'pengiriman_ba_inname', 'pengiriman_id', 'ba_inname_id');
    }
}
