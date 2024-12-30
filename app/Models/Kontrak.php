<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kontrak extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'progres',
        'bobot',
        'user_input_by',
        'file',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class)->whereNull('deleted_at');
    }

    public function kontrakProject()
    {
        return $this->hasMany(KontrakProject::class, 'kontrak_id');
    }

    public function userInputBy()
    {
        return $this->belongsTo(User::class, 'user_input_by');
    }
}
