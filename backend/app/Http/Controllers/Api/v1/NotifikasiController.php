<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $notif = Notifikasi::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return $this->success($notif);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notifikasi::where('user_id', $request->user()->id)
            ->where('is_read', false)->count();

        return $this->success(['count' => $count]);
    }

    public function markRead(Notifikasi $notifikasi): JsonResponse
    {
        abort_if($notifikasi->user_id !== request()->user()->id, 403);
        $notifikasi->update(['is_read' => true]);
        return $this->success($notifikasi);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        Notifikasi::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->success(null, 'Semua notifikasi telah dibaca');
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        $validated = $request->validate(['fcm_token' => 'required|string']);
        $request->user()->update(['fcm_token' => $validated['fcm_token']]);
        return $this->success(null, 'FCM token berhasil diperbarui');
    }
}
