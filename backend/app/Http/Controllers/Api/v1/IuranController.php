<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Jobs\SendFcmNotificationJob;
use App\Models\Iuran;
use App\Models\Warga;
use App\Services\MidtransService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IuranController extends Controller
{
    use ApiResponse;

    public function __construct(protected MidtransService $midtrans) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'warga') {
            $warga = $user->warga;
            $query = Iuran::with('warga.user')->where('warga_id', $warga?->id);
        } else {
            $admin = $user->admin;
            $query = Iuran::with('warga.user')
                ->whereHas('warga', fn($q) => $q->where('rt_rw_id', $admin?->rt_rw_id));
            if ($request->filled('warga_id')) {
                $query->where('warga_id', $request->warga_id);
            }
        }

        if ($request->filled('bulan')) $query->where('bulan', $request->bulan);
        if ($request->filled('status')) $query->where('status', $request->status);

        return $this->success($query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15));
    }

    public function tandaiLunas(Request $request, Warga $warga): JsonResponse
    {
        $validated = $request->validate([
            'bulan' => 'required|string|max:7',
            'nominal' => 'required|numeric|min:0',
        ]);

        Iuran::updateOrCreate(
            ['warga_id' => $warga->id, 'bulan' => $validated['bulan']],
            [
                'nominal' => $validated['nominal'],
                'status' => 'lunas',
                'metode' => 'manual',
                'tanggal_bayar' => now(),
                'diverifikasi_oleh' => $request->user()->id,
            ]
        );

        return $this->success(null, 'Iuran ditandai lunas');
    }

    public function bayar(Request $request): JsonResponse
    {
        $user = $request->user();
        $warga = $user->warga;
        if (!$warga) return $this->error('Hanya untuk akun warga', 403);

        $validated = $request->validate([
            'bulan_list' => 'required|array|min:1',
            'bulan_list.*' => 'string|max:7',
            'nominal' => 'required|numeric|min:0',
            'bukti_transfer' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'metode' => 'required|in:manual,qris',
        ]);

        if ($validated['metode'] === 'qris') {
            $charge = $this->midtrans->createQrisCharge(
                $warga->id, $warga->nama, (int) $validated['nominal'], $validated['bulan_list']
            );

            if (!$charge) {
                return $this->error('Gagal membuat pembayaran QRIS', 500);
            }

            $records = [];
            foreach ($validated['bulan_list'] as $bulan) {
                $records[] = Iuran::create([
                    'warga_id' => $warga->id,
                    'bulan' => $bulan,
                    'nominal' => $validated['nominal'] / count($validated['bulan_list']),
                    'status' => 'belum_bayar',
                    'metode' => 'qris',
                    'payment_url' => $charge['qr_string'],
                    'transaction_id' => $charge['transaction_id'],
                ]);
            }

            return $this->success([
                'iuran' => $records,
                'qr_string' => $charge['qr_string'],
                'transaction_id' => $charge['transaction_id'],
            ], 'Silakan scan QR untuk membayar', 201);
        }

        if (!$request->hasFile('bukti_transfer')) {
            return $this->error('Bukti transfer wajib diupload untuk metode manual', 422);
        }

        $path = $request->file('bukti_transfer')->store('iuran/bukti', 'public');

        $records = [];
        foreach ($validated['bulan_list'] as $bulan) {
            $records[] = Iuran::create([
                'warga_id' => $warga->id,
                'bulan' => $bulan,
                'nominal' => $validated['nominal'] / count($validated['bulan_list']),
                'status' => 'menunggu_verifikasi',
                'metode' => 'manual',
                'bukti_transfer' => $path,
                'tanggal_bayar' => now(),
            ]);
        }

        $adminUser = $warga->rtRw?->ketuaRt?->user;
        if ($adminUser) {
            SendFcmNotificationJob::dispatch($adminUser, 'Pembayaran Iuran', "{$warga->nama} mengajukan pembayaran iuran", 'iuran', null);
        }

        return $this->success($records, 'Pembayaran diajukan, menunggu verifikasi admin', 201);
    }

    public function verifikasi(Request $request, Iuran $iuran): JsonResponse
    {
        if ($iuran->status !== 'menunggu_verifikasi') {
            return $this->error('Iuran tidak dalam status menunggu verifikasi', 422);
        }

        $iuran->update([
            'status' => 'lunas',
            'diverifikasi_oleh' => $request->user()->id,
            'tanggal_bayar' => now(),
        ]);

        if ($iuran->warga?->user) {
            SendFcmNotificationJob::dispatch(
                $iuran->warga->user, 'Iuran Terverifikasi',
                "Pembayaran iuran bulan {$iuran->bulan} telah diverifikasi dan lunas",
                'iuran', (string) $iuran->id
            );
        }

        return $this->success($iuran->fresh()->load('warga.user'), 'Iuran diverifikasi');
    }

    public function qris(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'warga_id' => 'required|exists:warga,id',
            'bulan_list' => 'required|array|min:1',
            'bulan_list.*' => 'string|max:7',
            'nominal' => 'required|numeric|min:0',
        ]);

        $warga = Warga::findOrFail($validated['warga_id']);
        $charge = $this->midtrans->createQrisCharge(
            $warga->id, $warga->nama, (int) $validated['nominal'], $validated['bulan_list']
        );

        if (!$charge) return $this->error('Gagal generate QRIS', 500);

        foreach ($validated['bulan_list'] as $bulan) {
            Iuran::create([
                'warga_id' => $warga->id,
                'bulan' => $bulan,
                'nominal' => $validated['nominal'] / count($validated['bulan_list']),
                'status' => 'belum_bayar',
                'metode' => 'qris',
                'payment_url' => $charge['qr_string'],
                'transaction_id' => $charge['transaction_id'],
            ]);
        }

        return $this->success($charge, 'QRIS berhasil dibuat');
    }

    public function callback(Request $request): JsonResponse
    {
        $result = $this->midtrans->verifyCallback($request->all());
        if (!$result || !$result['is_success']) {
            return $this->error('Invalid callback', 400);
        }

        Iuran::where('transaction_id', $result['transaction_id'])
            ->where('status', 'belum_bayar')
            ->update([
                'status' => 'lunas',
                'tanggal_bayar' => now(),
            ]);

        return $this->success(null, 'Callback processed');
    }

    public function laporan(Request $request)
    {
        $user = $request->user();
        $admin = $user->admin;
        $query = Iuran::with('warga')
            ->whereHas('warga', fn($q) => $q->where('rt_rw_id', $admin?->rt_rw_id))
            ->where('status', 'lunas');

        if ($request->filled('bulan')) $query->where('bulan', $request->bulan);

        $data = $query->orderBy('bulan')->get();

        $filename = 'laporan-iuran-' . now()->format('Ymd') . '.csv';
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['No', 'NIK', 'Nama', 'Bulan', 'Nominal', 'Metode', 'Tanggal Bayar']);

        foreach ($data as $i => $d) {
            fputcsv($handle, [
                $i + 1, $d->warga->nik, $d->warga->nama,
                $d->bulan, $d->nominal, $d->metode,
                $d->tanggal_bayar?->format('Y-m-d'),
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
