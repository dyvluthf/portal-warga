<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Keuangan;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KeuanganController extends Controller
{
    use ApiResponse;

    protected function getRtRwId(Request $request): int
    {
        $user = $request->user();
        if ($user->role === 'super_admin') {
            return (int) ($request->input('rt_rw_id', optional($user->admin)->rt_rw_id ?? 0));
        }
        return $user->admin->rt_rw_id;
    }

    public function dashboard(Request $request): JsonResponse
    {
        $rtRwId = $this->getRtRwId($request);
        $query = Keuangan::where('rt_rw_id', $rtRwId);

        if ($request->filled('bulan')) {
            [$tahun, $bulan] = explode('-', $request->bulan);
            $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
        }

        $pemasukan = (clone $query)->where('jenis', 'pemasukan')->sum('jumlah');
        $pengeluaran = (clone $query)->where('jenis', 'pengeluaran')->sum('jumlah');

        return $this->success([
            'saldo' => $pemasukan - $pengeluaran,
            'total_pemasukan' => $pemasukan,
            'total_pengeluaran' => $pengeluaran,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $rtRwId = $this->getRtRwId($request);
        $query = Keuangan::with('dicatatOleh')->where('rt_rw_id', $rtRwId);

        if ($request->filled('jenis')) $query->where('jenis', $request->jenis);
        if ($request->filled('bulan')) {
            [$tahun, $bulan] = explode('-', $request->bulan);
            $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
        }

        return $this->success($query->orderBy('tanggal', 'desc')->paginate($request->per_page ?? 15));
    }

    public function pengeluaran(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
            'kategori' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
            'bukti_foto' => 'nullable|file|mimes:jpeg,png,jpg|max:5120',
        ]);

        $keuangan = Keuangan::create([
            'jenis' => 'pengeluaran',
            'judul' => $validated['judul'],
            'jumlah' => $validated['jumlah'],
            'kategori' => $validated['kategori'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
            'rt_rw_id' => $this->getRtRwId($request),
            'tanggal' => today(),
            'dicatat_oleh' => $request->user()->id,
        ]);

        if ($request->hasFile('bukti_foto')) {
            $keuangan->update(['bukti_foto' => $request->file('bukti_foto')->store('keuangan', 'public')]);
        }

        return $this->success($keuangan->fresh(), 'Pengeluaran dicatat', 201);
    }

    public function laporan(Request $request)
    {
        $rtRwId = $this->getRtRwId($request);
        $query = Keuangan::where('rt_rw_id', $rtRwId)->orderBy('tanggal');

        if ($request->filled('bulan')) {
            [$tahun, $bulan] = explode('-', $request->bulan);
            $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
        }

        $data = $query->get();

        $filename = 'laporan-keuangan-' . now()->format('Ymd') . '.csv';
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['No', 'Jenis', 'Judul', 'Kategori', 'Jumlah', 'Tanggal', 'Keterangan']);

        foreach ($data as $i => $d) {
            fputcsv($handle, [
                $i + 1, $d->jenis, $d->judul, $d->kategori,
                $d->jumlah, $d->tanggal->format('Y-m-d'), $d->keterangan,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
