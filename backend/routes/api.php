<?php

use App\Http\Controllers\Api\v1\ActivityLogController;
use App\Http\Controllers\Api\v1\AdminController;
use App\Http\Controllers\Api\v1\AgendaController;
use App\Http\Controllers\Api\v1\Auth\AuthController;
use App\Http\Controllers\Api\v1\BeritaController;
use App\Http\Controllers\Api\v1\DashboardController;
use App\Http\Controllers\Api\v1\GaleriController;
use App\Http\Controllers\Api\v1\IuranController;
use App\Http\Controllers\Api\v1\KeuanganController;
use App\Http\Controllers\Api\v1\NotifikasiController;
use App\Http\Controllers\Api\v1\PengaduanController;
use App\Http\Controllers\Api\v1\RtRwController;
use App\Http\Controllers\Api\v1\SuratController;
use App\Http\Controllers\Api\v1\WargaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [WargaController::class, 'profile']);
    Route::put('/profile', [WargaController::class, 'updateProfile']);

    Route::apiResource('rt-rw', RtRwController::class);
    Route::get('/rt-rw/{rtRw}/warga', [RtRwController::class, 'warga']);

    Route::get('/warga/export', [WargaController::class, 'export']);
    Route::post('/warga/import', [WargaController::class, 'import']);
    Route::apiResource('warga', WargaController::class);

    Route::post('/berita/{berita}/publish', [BeritaController::class, 'publish']);
    Route::post('/berita/{berita}/draft', [BeritaController::class, 'draft']);
    Route::apiResource('berita', BeritaController::class);

    Route::post('/pengaduan/{pengaduan}/proses', [PengaduanController::class, 'proses']);
    Route::post('/pengaduan/{pengaduan}/selesai', [PengaduanController::class, 'selesai']);
    Route::post('/pengaduan/{pengaduan}/tolak', [PengaduanController::class, 'tolak']);
    Route::post('/pengaduan/{pengaduan}/rating', [PengaduanController::class, 'rating']);
    Route::apiResource('pengaduan', PengaduanController::class);

    Route::post('/surat/{surat}/setujui', [SuratController::class, 'setujui']);
    Route::post('/surat/{surat}/terbitkan', [SuratController::class, 'terbitkan']);
    Route::post('/surat/{surat}/tolak', [SuratController::class, 'tolak']);
    Route::get('/surat/{surat}/download', [SuratController::class, 'download']);
    Route::apiResource('surat', SuratController::class);

    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
    Route::get('/notifikasi/unread', [NotifikasiController::class, 'unreadCount']);
    Route::post('/notifikasi/{notifikasi}/read', [NotifikasiController::class, 'markRead']);
    Route::post('/notifikasi/read-all', [NotifikasiController::class, 'markAllRead']);
    Route::post('/notifikasi/fcm-token', [NotifikasiController::class, 'updateFcmToken']);

    Route::get('/iuran/laporan', [IuranController::class, 'laporan']);
    Route::post('/iuran/{warga}/tandai-lunas', [IuranController::class, 'tandaiLunas']);
    Route::post('/iuran/bayar', [IuranController::class, 'bayar']);
    Route::put('/iuran/{iuran}/verifikasi', [IuranController::class, 'verifikasi']);
    Route::post('/iuran/qris', [IuranController::class, 'qris']);
    Route::post('/iuran/callback', [IuranController::class, 'callback'])->withoutMiddleware('auth:sanctum');
    Route::get('/iuran', [IuranController::class, 'index']);

    Route::get('/keuangan/dashboard', [KeuanganController::class, 'dashboard']);
    Route::post('/keuangan/pengeluaran', [KeuanganController::class, 'pengeluaran']);
    Route::get('/keuangan/laporan', [KeuanganController::class, 'laporan']);
    Route::get('/keuangan', [KeuanganController::class, 'index']);

    Route::post('/agenda/{agenda}/rsvp', [AgendaController::class, 'rsvp']);
    Route::get('/agenda/{agenda}/peserta', [AgendaController::class, 'peserta']);
    Route::apiResource('agenda', AgendaController::class);

    Route::get('/galeri/{galeriAlbum}', [GaleriController::class, 'show']);
    Route::delete('/galeri/{galeriAlbum}', [GaleriController::class, 'destroy']);
    Route::get('/galeri', [GaleriController::class, 'index']);
    Route::post('/galeri', [GaleriController::class, 'store']);

    Route::get('/dashboard/admin', [DashboardController::class, 'admin']);
    Route::get('/dashboard/warga', [DashboardController::class, 'warga']);

    Route::middleware('role:super_admin')->group(function () {
        Route::apiResource('admin', AdminController::class);
        Route::get('/admin/activity-logs', [ActivityLogController::class, 'index']);
    });
});
