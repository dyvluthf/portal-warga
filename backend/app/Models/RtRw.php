<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RtRw extends Model
{
    use HasFactory;

    protected $table = 'rt_rw';

    protected $fillable = [
        'nomor_rt', 'nomor_rw', 'nama_kelurahan', 'kecamatan',
        'ketua_rt_id', 'sekretaris_id', 'bendahara_id',
    ];

    public function ketuaRt()
    {
        return $this->belongsTo(Warga::class, 'ketua_rt_id');
    }

    public function sekretaris()
    {
        return $this->belongsTo(Warga::class, 'sekretaris_id');
    }

    public function bendahara()
    {
        return $this->belongsTo(Warga::class, 'bendahara_id');
    }

    public function warga()
    {
        return $this->hasMany(Warga::class, 'rt_rw_id');
    }
}
