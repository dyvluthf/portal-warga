<?php

namespace App\Policies;

use App\Models\Iuran;
use App\Models\User;

class IuranPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw', 'warga']);
    }

    public function view(User $user, Iuran $iuran): bool
    {
        if ($user->role === 'super_admin') return true;
        if ($user->role === 'admin_rt_rw') return $iuran->warga->rt_rw_id === $user->admin->rt_rw_id;
        return $user->warga && $user->warga->id === $iuran->warga_id;
    }

    public function verifikasi(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }
}
