<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        protected User $user,
        protected string $judul,
        protected string $pesan,
        protected ?string $tipe = null,
        protected ?string $referensiId = null,
    ) {}

    public function handle(FcmService $fcm): void
    {
        $fcm->send($this->user, $this->judul, $this->pesan, $this->tipe, $this->referensiId);
    }
}
