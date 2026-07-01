<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateSuratPdfJob;
use App\Jobs\SendFcmNotificationJob;
use App\Models\Surat;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SuratController extends Controller
{
    use ApiResponse;

    protected $jenisSurat = ['SKTM', 'Keterangan Domisili', 'Keterangan Usaha', 'Izin Keramaian', 'Lainnya'];

    public function index(Request $request): JsonResponse
    {
        $query = Surat::with('warga.user');
        $user = $request->user();

        if ($user->role === 'warga') {
            $warga = $user->warga;
            $query->where('warga_id', $warga?->id);
        } elseif ($user->role === 'admin_rt_rw') {
            $admin = $user->admin;
            $query->whereHas('warga', fn($q) => $q->where('rt_rw_id', $admin->rt_rw_id));
        }

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('jenis_surat')) $query->where('jenis_surat', $request->jenis_surat);

        return $this->success($query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $warga = $user->warga;
        if (!$warga) return $this->error('Hanya untuk akun warga', 403);

        $validated = $request->validate([
            'jenis_surat' => 'required|string|in:' . implode(',', $this->jenisSurat),
            'data_form' => 'required|array',
            'data_form.tempat_lahir' => 'nullable|string',
            'data_form.tanggal_lahir' => 'nullable|string',
            'data_form.jenis_kelamin' => 'nullable|string',
            'data_form.keterangan' => 'nullable|string',
            'dokumen_pendukung' => 'nullable|array|max:5',
            'dokumen_pendukung.*' => 'file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $dokumenPaths = [];
        if ($request->hasFile('dokumen_pendukung')) {
            foreach ($request->file('dokumen_pendukung') as $file) {
                $dokumenPaths[] = $file->store('surat/dokumen', 'public');
            }
        }

        $surat = Surat::create([
            'warga_id' => $warga->id,
            'jenis_surat' => $validated['jenis_surat'],
            'data_form' => array_merge($validated['data_form'], [
                'nama' => $warga->nama,
                'nik' => $warga->nik,
                'alamat' => $warga->alamat,
            ]),
            'dokumen_pendukung' => $dokumenPaths,
        ]);

        $this->notifyAdmin($surat);

        return $this->success($surat->load('warga.user'), 'Surat berhasil diajukan', 201);
    }

    public function show(Surat $surat): JsonResponse
    {
        Gate::authorize('view', $surat);
        $surat->load('warga.user');
        return $this->success($surat);
    }

    public function setujui(Surat $surat): JsonResponse
    {
        Gate::authorize('setujui', $surat);
        $surat->update(['status' => 'disetujui']);
        return $this->success($surat->fresh()->load('warga.user'), 'Surat disetujui');
    }

    public function terbitkan(Surat $surat): JsonResponse
    {
        Gate::authorize('terbitkan', $surat);
        $rt = $surat->warga->rtRw;
        $bulanRomawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $nomor = sprintf(
            '%03d/%s-%s/%s/%s',
            $surat->id,
            $rt?->nomor_rt ?? '00',
            $rt?->nomor_rw ?? '00',
            $bulanRomawi[(int) now()->format('m') - 1],
            now()->format('Y')
        );

        $surat->update(['status' => 'diterbitkan', 'nomor_surat' => $nomor]);
        GenerateSuratPdfJob::dispatch($surat);

        if ($surat->warga?->user) {
            SendFcmNotificationJob::dispatch(
                $surat->warga->user, 'Surat Diterbitkan',
                "Surat {$surat->jenis_surat} telah diterbitkan. Nomor: {$nomor}",
                'surat', (string) $surat->id
            );
        }

        return $this->success($surat->fresh()->load('warga.user'), 'Surat berhasil diterbitkan');
    }

    public function tolak(Request $request, Surat $surat): JsonResponse
    {
        Gate::authorize('tolak', $surat);
        $validated = $request->validate(['alasan_penolakan' => 'required|string']);
        $surat->update(['status' => 'ditolak', 'alasan_penolakan' => $validated['alasan_penolakan']]);

        if ($surat->warga?->user) {
            SendFcmNotificationJob::dispatch(
                $surat->warga->user, 'Surat Ditolak',
                "Surat {$surat->jenis_surat} ditolak: {$validated['alasan_penolakan']}",
                'surat', (string) $surat->id
            );
        }

        return $this->success($surat->fresh()->load('warga.user'), 'Surat ditolak');
    }

    public function download(Surat $surat)
    {
        Gate::authorize('view', $surat);
        if (!$surat->file_pdf_path || !Storage::disk('public')->exists($surat->file_pdf_path)) {
            return $this->error('File PDF belum tersedia', 404);
        }
        return Storage::disk('public')->download($surat->file_pdf_path, "surat-{$surat->nomor_surat}.pdf");
    }

    protected function notifyAdmin(Surat $surat): void
    {
        $rt = $surat->warga->rtRw;
        if ($rt?->ketuaRt?->user) {
            SendFcmNotificationJob::dispatch(
                $rt->ketuaRt->user, 'Pengajuan Surat Baru',
                "Surat {$surat->jenis_surat} dari {$surat->warga->nama}",
                'surat', (string) $surat->id
            );
        }
    }
}
