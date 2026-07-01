<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Berita;
use App\Models\Iuran;
use App\Models\Pengaduan;
use App\Models\Surat;
use App\Models\Warga;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    public function admin(Request $request): JsonResponse
    {
        $user = $request->user();
        $rtRwId = $user->role === 'super_admin' ? null : $user->admin?->rt_rw_id;

        $cacheKey = 'dashboard_admin_' . ($rtRwId ?? 'all');

        $data = Cache::remember($cacheKey, 600, function () use ($rtRwId) {
            $wargaQuery = Warga::query();
            $pengaduanQuery = Pengaduan::query();
            $suratQuery = Surat::query();
            $iuranQuery = Iuran::where('status', 'lunas');
            $agendaQuery = Agenda::query();

            if ($rtRwId) {
                $wargaQuery->where('rt_rw_id', $rtRwId);
                $pengaduanQuery->whereHas('warga', fn($q) => $q->where('rt_rw_id', $rtRwId));
                $suratQuery->whereHas('warga', fn($q) => $q->where('rt_rw_id', $rtRwId));
                $iuranQuery->whereHas('warga', fn($q) => $q->where('rt_rw_id', $rtRwId));
                $agendaQuery->where('rt_rw_id', $rtRwId);
            }

            $totalWarga = $wargaQuery->count();
            $pengaduanBaru = (clone $pengaduanQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where('status', 'baru')->count();
            $pengaduanSelesai = (clone $pengaduanQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where('status', 'selesai')->count();
            $suratPending = (clone $suratQuery)->where('status', 'pending')->count();
            $suratDiterbitkan = (clone $suratQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where('status', 'diterbitkan')->count();
            $iuranTerkumpul = (clone $iuranQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('nominal');

            $trenPengaduan = [];
            $trenSurat = [];
            $trenIuran = [];

            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $bulan = $date->format('Y-m');

                $pQuery = clone $pengaduanQuery;
                $sQuery = clone $suratQuery;
                $iQuery = clone $iuranQuery;

                $trenPengaduan[] = [
                    'bulan' => $bulan,
                    'total' => (clone $pQuery)->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                ];
                $trenSurat[] = [
                    'bulan' => $bulan,
                    'total' => (clone $sQuery)->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                ];
                $trenIuran[] = [
                    'bulan' => $bulan,
                    'total' => (double) (clone $iQuery)->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->sum('nominal'),
                ];
            }

            $agendaHariIni = (clone $agendaQuery)->whereDate('tanggal', today())->get(['id', 'nama', 'jam', 'lokasi']);
            $pengaduanTerbaru = (clone $pengaduanQuery)->with('warga:id,nama')->latest()->limit(5)->get(['id', 'kategori', 'status', 'created_at', 'warga_id']);

            return [
                'total_warga' => $totalWarga,
                'pengaduan_baru' => $pengaduanBaru,
                'pengaduan_selesai' => $pengaduanSelesai,
                'surat_pending' => $suratPending,
                'surat_diterbitkan' => $suratDiterbitkan,
                'iuran_terkumpul' => (double) $iuranTerkumpul,
                'tren_pengaduan' => $trenPengaduan,
                'tren_surat' => $trenSurat,
                'tren_iuran' => $trenIuran,
                'agenda_hari_ini' => $agendaHariIni,
                'pengaduan_terbaru' => $pengaduanTerbaru,
            ];
        });

        return $this->success($data);
    }

    public function warga(Request $request): JsonResponse
    {
        $user = $request->user();
        $warga = $user->warga;
        if (!$warga) return $this->error('Hanya untuk akun warga', 403);

        $cacheKey = 'dashboard_warga_' . $warga->id;

        $data = Cache::remember($cacheKey, 600, function () use ($warga) {
            $pengaduanAktif = Pengaduan::where('warga_id', $warga->id)->whereIn('status', ['baru', 'diproses'])->count();
            $suratPending = Surat::where('warga_id', $warga->id)->whereIn('status', ['pending', 'disetujui'])->count();

            $iuranBln = Iuran::where('warga_id', $warga->id)->where('bulan', now()->format('Y-m'))->first();

            $beritaTerbaru = Berita::where('status', 'publish')->latest()->limit(3)->get(['id', 'judul', 'kategori', 'gambar', 'tanggal_publish']);
            $agendaTerdekat = Agenda::where('rt_rw_id', $warga->rt_rw_id)->whereDate('tanggal', '>=', today())->orderBy('tanggal')->limit(3)->get(['id', 'nama', 'tanggal', 'jam', 'lokasi']);

            return [
                'pengaduan_aktif' => $pengaduanAktif,
                'surat_pending' => $suratPending,
                'iuran_bulan_ini' => $iuranBln ? [
                    'status' => $iuranBln->status,
                    'nominal' => (double) $iuranBln->nominal,
                ] : ['status' => 'belum_bayar', 'nominal' => 0],
                'berita_terbaru' => $beritaTerbaru,
                'agenda_terdekat' => $agendaTerdekat,
            ];
        });

        return $this->success($data);
    }
}
