<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected string $serverKey;

    public function __construct()
    {
        $this->serverKey = config('services.fcm.server_key', '');
    }

    public function send(User $user, string $judul, string $pesan, string $tipe = null, string $referensiId = null): void
    {
        Notifikasi::create([
            'user_id' => $user->id,
            'judul' => $judul,
            'pesan' => $pesan,
            'tipe' => $tipe,
            'referensi_id' => $referensiId,
        ]);

        if (!$user->fcm_token || !$this->serverKey) {
            return;
        }

        try {
            Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $user->fcm_token,
                'notification' => [
                    'title' => $judul,
                    'body' => $pesan,
                ],
                'data' => [
                    'tipe' => $tipe,
                    'referensi_id' => (string) $referensiId,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('FCM send failed: ' . $e->getMessage());
        }
    }
}
