<?php

namespace App\Providers;

use App\Models\Agenda;
use App\Models\Berita;
use App\Models\GaleriAlbum;
use App\Models\Iuran;
use App\Models\Keuangan;
use App\Models\Pengaduan;
use App\Models\RtRw;
use App\Models\Surat;
use App\Models\Warga;
use App\Observers\LogIuranObserver;
use App\Observers\LogKeuanganObserver;
use App\Observers\LogPengaduanObserver;
use App\Observers\LogSuratObserver;
use App\Policies\AgendaPolicy;
use App\Policies\BeritaPolicy;
use App\Policies\GaleriPolicy;
use App\Policies\IuranPolicy;
use App\Policies\KeuanganPolicy;
use App\Policies\PengaduanPolicy;
use App\Policies\RtRwPolicy;
use App\Policies\SuratPolicy;
use App\Policies\WargaPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(RtRw::class, RtRwPolicy::class);
        Gate::policy(Warga::class, WargaPolicy::class);
        Gate::policy(Berita::class, BeritaPolicy::class);
        Gate::policy(Pengaduan::class, PengaduanPolicy::class);
        Gate::policy(Surat::class, SuratPolicy::class);
        Gate::policy(Iuran::class, IuranPolicy::class);
        Gate::policy(Keuangan::class, KeuanganPolicy::class);
        Gate::policy(Agenda::class, AgendaPolicy::class);
        Gate::policy(GaleriAlbum::class, GaleriPolicy::class);

        Pengaduan::observe(LogPengaduanObserver::class);
        Surat::observe(LogSuratObserver::class);
        Iuran::observe(LogIuranObserver::class);
        Keuangan::observe(LogKeuanganObserver::class);
    }
}
