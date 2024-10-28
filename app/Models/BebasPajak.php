<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BebasPajak extends Model
{
    use HasFactory;

    protected $table = 'project_bebas_pajak';

    protected $fillable = [
        'project_id',
        'bebas_pajak',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
