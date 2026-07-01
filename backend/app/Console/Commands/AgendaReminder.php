<?php

namespace App\Console\Commands;

use App\Jobs\SendFcmNotificationJob;
use App\Models\Agenda;
use Illuminate\Console\Command;

class AgendaReminder extends Command
{
    protected $signature = 'agenda:reminder';
    protected $description = 'Send FCM reminder for tomorrow\'s agendas';

    public function handle(): void
    {
        $agendas = Agenda::with('rtRw')->whereDate('tanggal', today()->addDay())->get();

        foreach ($agendas as $agenda) {
            $wargaList = \App\Models\Warga::where('rt_rw_id', $agenda->rt_rw_id)
                ->whereHas('user')
                ->with('user')
                ->get();

            foreach ($wargaList as $warga) {
                if ($warga->user) {
                    SendFcmNotificationJob::dispatch(
                        $warga->user,
                        'Reminder Agenda',
                        "Besok: {$agenda->nama} pukul {$agenda->jam} di {$agenda->lokasi}",
                        'agenda',
                        (string) $agenda->id
                    );
                }
            }

            $this->info("Reminder sent for: {$agenda->nama}");
        }

        $this->info('Agenda reminders completed.');
    }
}
