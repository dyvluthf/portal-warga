<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Iuran;
use Illuminate\Support\Facades\Auth;

class LogIuranObserver
{
    public function updated(Iuran $iuran): void
    {
        if (!$iuran->isDirty('status') || $iuran->status !== 'lunas' || !Auth::check()) return;

        ActivityLog::create([
            'user_id' => Auth::id(),
            'aksi' => 'lunas_iuran',
            'model' => 'Iuran',
            'model_id' => (string) $iuran->id,
            'keterangan' => "Iuran {$iuran->bulan} lunas untuk warga ID {$iuran->warga_id}",
        ]);
    }
}
