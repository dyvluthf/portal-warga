<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warga extends Model
{
    use HasFactory;

    protected $table = 'warga';

    protected $fillable = [
        'nik', 'nama', 'alamat', 'rt_id', 'rw_id', 'rt_rw_id',
        'no_telp', 'foto_profil', 'status_verifikasi', 'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rtRw()
    {
        return $this->belongsTo(RtRw::class, 'rt_rw_id');
    }
}
