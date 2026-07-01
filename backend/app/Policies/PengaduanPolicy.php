<?php

namespace App\Policies;

use App\Models\Pengaduan;
use App\Models\User;

class PengaduanPolicy
{
    public function view(User $user, Pengaduan $pengaduan): bool
    {
        if ($user->role === 'super_admin') return true;
        if ($user->role === 'admin_rt_rw') {
            $admin = $user->admin;
            return $admin && $pengaduan->warga->rt_rw_id === $admin->rt_rw_id;
        }
        return $user->warga && $user->warga->id === $pengaduan->warga_id;
    }

    public function proses(User $user, Pengaduan $pengaduan): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }

    public function selesai(User $user, Pengaduan $pengaduan): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }

    public function tolak(User $user, Pengaduan $pengaduan): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }
}
