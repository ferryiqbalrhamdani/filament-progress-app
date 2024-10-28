<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penagihan extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_input_by',
        'progres',
        'bobot',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class)->whereNull('deleted_at');
    }

    public function userInputBy()
    {
        return $this->belongsTo(User::class, 'user_input_by');
    }

    public function penagihanProject()
    {
        return $this->hasMany(PenagihanProject::class, 'penagihan_id')->orderBy('jenis_penagihan', 'desc');
    }
}
