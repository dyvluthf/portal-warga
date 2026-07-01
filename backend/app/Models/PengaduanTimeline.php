<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaduanTimeline extends Model
{
    protected $table = 'pengaduan_timeline';
    public $timestamps = false;

    protected $fillable = [
        'pengaduan_id', 'status', 'keterangan', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function pengaduan()
    {
        return $this->belongsTo(Pengaduan::class);
    }
}
