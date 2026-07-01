<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Pengaduan;
use Illuminate\Support\Facades\Auth;

class LogPengaduanObserver
{
    public function updated(Pengaduan $pengaduan): void
    {
        if (!$pengaduan->isDirty('status') || !Auth::check()) return;

        $aksi = match ($pengaduan->status) {
            'diproses' => 'proses_pengaduan',
            'selesai' => 'selesai_pengaduan',
            'ditolak' => 'tolak_pengaduan',
            default => 'ubah_status_pengaduan',
        };
        ActivityLog::create([
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'model' => 'Pengaduan',
            'model_id' => (string) $pengaduan->id,
            'keterangan' => "Status pengaduan {$pengaduan->kategori} -> {$pengaduan->status}",
        ]);
    }
}
