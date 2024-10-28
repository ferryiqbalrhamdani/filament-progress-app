<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marcendiser extends Model
{
    use HasFactory;

    protected $fillable = [
        'marcendiser_po_id',
        'project_id',
        'user_input_by',
        'progres',
        'jumlah_item',
        'jumlah_ea',
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

    public function pos()
    {
        return $this->belongsToMany(Po::class);
    }
    public function invoices()
    {
        return $this->belongsToMany(Invoice::class);
    }

    public function productions()
    {
        return $this->belongsToMany(Production::class);
    }
    public function deliveries()
    {
        return $this->belongsToMany(Delivery::class);
    }
    public function readyStocks()
    {
        return $this->belongsToMany(ReadyStock::class);
    }
    public function receiveds()
    {
        return $this->belongsToMany(Received::class);
    }
}
