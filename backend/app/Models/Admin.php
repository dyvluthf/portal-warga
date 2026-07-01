<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama', 'email', 'role', 'rt_rw_id', 'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
