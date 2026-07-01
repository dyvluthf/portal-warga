<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Keuangan;
use Illuminate\Support\Facades\Auth;

class LogKeuanganObserver
{
    public function created(Keuangan $keuangan): void
    {
        if (!Auth::check()) return;

        ActivityLog::create([
            'user_id' => Auth::id(),
            'aksi' => 'catat_pengeluaran',
            'model' => 'Keuangan',
            'model_id' => (string) $keuangan->id,
            'keterangan' => "Pengeluaran: {$keuangan->judul} Rp {$keuangan->jumlah}",
        ]);
    }
}
