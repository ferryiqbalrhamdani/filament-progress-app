<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KontrakProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'status',
    ];

    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class);
    }
}
