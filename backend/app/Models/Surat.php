<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    use HasFactory;

    protected $table = 'surat';

    protected $fillable = [
        'warga_id', 'jenis_surat', 'nomor_surat', 'data_form',
        'dokumen_pendukung', 'status', 'alasan_penolakan', 'file_pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'data_form' => 'array',
            'dokumen_pendukung' => 'array',
        ];
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class);
    }
}
