<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Berita extends Model
{
    use HasFactory;

    protected $table = 'berita';

    protected $fillable = [
        'judul', 'slug', 'konten', 'kategori', 'gambar',
        'status', 'penulis_id', 'tanggal_publish',
    ];

    protected static function booted()
    {
        static::creating(function ($berita) {
            if (empty($berita->slug)) {
                $berita->slug = Str::slug($berita->judul) . '-' . Str::random(6);
            }
        });
    }

    public function penulis()
    {
        return $this->belongsTo(User::class, 'penulis_id');
    }
}
