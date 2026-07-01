<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Surat;
use Illuminate\Support\Facades\Auth;

class LogSuratObserver
{
    public function updated(Surat $surat): void
    {
        if (!$surat->isDirty('status') || !Auth::check()) return;

        $aksi = match ($surat->status) {
            'disetujui' => 'setujui_surat',
            'diterbitkan' => 'terbitkan_surat',
            'ditolak' => 'tolak_surat',
            default => 'ubah_status_surat',
        };
        ActivityLog::create([
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'model' => 'Surat',
            'model_id' => (string) $surat->id,
            'keterangan' => "Status surat {$surat->jenis_surat} -> {$surat->status}",
        ]);
    }
}
