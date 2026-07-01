<?php

namespace App\Policies;

use App\Models\Surat;
use App\Models\User;

class SuratPolicy
{
    public function view(User $user, Surat $surat): bool
    {
        if ($user->role === 'super_admin') return true;
        if ($user->role === 'admin_rt_rw') {
            $admin = $user->admin;
            return $admin && $surat->warga->rt_rw_id === $admin->rt_rw_id;
        }
        return $user->warga && $user->warga->id === $surat->warga_id;
    }

    public function setujui(User $user, Surat $surat): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }

    public function terbitkan(User $user, Surat $surat): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }

    public function tolak(User $user, Surat $surat): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }
}
