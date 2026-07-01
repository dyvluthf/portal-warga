<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GaleriFoto extends Model
{
    use HasFactory;

    protected $table = 'galeri_foto';

    protected $fillable = [
        'album_id', 'path_foto', 'caption', 'urutan',
    ];

    public function album()
    {
        return $this->belongsTo(GaleriAlbum::class, 'album_id');
    }
}
