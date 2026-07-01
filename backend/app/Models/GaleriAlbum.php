<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GaleriAlbum extends Model
{
    use HasFactory;

    protected $table = 'galeri_album';

    protected $fillable = [
        'nama_album', 'rt_rw_id', 'dibuat_oleh',
    ];

    public function rtRw()
    {
        return $this->belongsTo(RtRw::class);
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function foto()
    {
        return $this->hasMany(GaleriFoto::class, 'album_id')->orderBy('urutan');
    }
}
