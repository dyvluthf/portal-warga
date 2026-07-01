<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Iuran extends Model
{
    use HasFactory;

    protected $table = 'iuran';

    protected $fillable = [
        'warga_id', 'bulan', 'nominal', 'status', 'metode',
        'bukti_transfer', 'tanggal_bayar', 'diverifikasi_oleh',
        'payment_url', 'transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'tanggal_bayar' => 'datetime',
        ];
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class);
    }

    public function diverifikasiOleh()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
