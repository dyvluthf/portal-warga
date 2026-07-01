<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaKehadiran extends Model
{
    use HasFactory;

    protected $table = 'agenda_kehadiran';

    protected $fillable = [
        'agenda_id', 'warga_id', 'status_rsvp',
    ];

    public function agenda()
    {
        return $this->belongsTo(Agenda::class);
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class);
    }
}
