<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    use HasFactory;

    protected $table = 'pengaduan';

    protected $fillable = [
        'warga_id', 'kategori', 'deskripsi', 'foto',
        'lokasi_lat', 'lokasi_lng', 'lokasi_manual',
        'status', 'tanggapan_admin', 'rating', 'ulasan', 'alasan_penolakan',
    ];

    protected function casts(): array
    {
        return [
            'foto' => 'array',
        ];
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class);
    }

    public function timeline()
    {
        return $this->hasMany(PengaduanTimeline::class)->orderBy('created_at');
    }
}
