<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    protected $table = 'agenda';

    protected $fillable = [
        'nama', 'tanggal', 'jam', 'lokasi', 'deskripsi',
        'rt_rw_id', 'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam' => 'string',
        ];
    }

    public function rtRw()
    {
        return $this->belongsTo(RtRw::class);
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function peserta()
    {
        return $this->belongsToMany(Warga::class, 'agenda_kehadiran')
            ->withPivot('status_rsvp')
            ->withTimestamps();
    }

    public function kehadiran()
    {
        return $this->hasMany(AgendaKehadiran::class);
    }

    public function scopeFilterPeriode($query, ?string $periode)
    {
        return match ($periode) {
            'hari_ini' => $query->whereDate('tanggal', today()),
            'minggu_ini' => $query->whereBetween('tanggal', [today()->startOfWeek(), today()->endOfWeek()]),
            'bulan_ini' => $query->whereMonth('tanggal', today()->month)->whereYear('tanggal', today()->year),
            default => $query,
        };
    }
}
