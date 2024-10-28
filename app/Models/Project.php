<?php

namespace App\Models;

use App\Models\ProjectSertifikatProduk;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory, Sluggable;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        // step 1
        'nama_pengadaan',
        'slug',
        'no_up',
        'jenis_lelang_id',
        'company_id',
        'jenis_anggaran_id',
        'pic_id',
        'vendor_id',
        'tahun_anggaran',
        'deskripsi',

        // step 2
        'bebas_pajak',
        'bebas_pajak_khusus',
        'asal_brand_id',
        'asal_brand_khusus',
        'garansi',
        'payment_term',

        // step 3
        'no_kontrak',
        'nilai_kontrak',
        'tanggal_kontrak',
        'tanggal_jatuh_tempo',

        'progres',
        'status',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'nama_pengadaan'
            ]
        ];
    }

    // belongsToMany
    public function vendors()
    {
        return $this->belongsToMany(Vendor::class);
    }
    public function sertifikatProduk()
    {
        return $this->belongsToMany(SertifikatProduk::class);
    }
    public function termins()
    {
        return $this->hasMany(ProjectTermin::class, 'project_id');
    }


    // belongsTo
    public function asalBrand()
    {
        return $this->belongsTo(AsalBrand::class);
    }
    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function instansi()
    {
        return $this->belongsTo(Instansi::class);
    }
    public function jenisLelang()
    {
        return $this->belongsTo(JenisLelang::class);
    }
    public function jenisAnggaran()
    {
        return $this->belongsTo(JenisAnggaran::class);
    }


    // hasOne
    public function bebasPajak()
    {
        return $this->hasOne(BebasPajak::class);
    }

    public function lamaGaransi()
    {
        return $this->hasOne(ProjectLamaGaransi::class, 'project_id');
    }

    public function projectDp()
    {
        return $this->hasOne(ProjectDp::class, 'project_id');
    }

    public function kontrak()
    {
        return $this->hasOne(Kontrak::class, 'project_id');
    }
    public function penagihan()
    {
        return $this->hasOne(Penagihan::class, 'project_id');
    }
    public function pengiriman()
    {
        return $this->hasOne(Pengiriman::class, 'project_id');
    }
    public function marcendiser()
    {
        return $this->hasOne(Marcendiser::class, 'project_id');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
