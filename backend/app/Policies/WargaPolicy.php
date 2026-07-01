<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warga;

class WargaPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }

    public function view(User $user, Warga $warga): bool
    {
        if ($user->role === 'super_admin') return true;
        if ($user->role === 'admin_rt_rw') {
            $admin = $user->admin;
            return $admin && $warga->rt_rw_id === $admin->rt_rw_id;
        }
        return $user->warga && $user->warga->id === $warga->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }

    public function update(User $user, Warga $warga): bool
    {
        if ($user->role === 'super_admin') return true;
        if ($user->role === 'admin_rt_rw') {
            $admin = $user->admin;
            return $admin && $warga->rt_rw_id === $admin->rt_rw_id;
        }
        return false;
    }

    public function delete(User $user, Warga $warga): bool
    {
        if ($user->role === 'super_admin') return true;
        if ($user->role === 'admin_rt_rw') {
            $admin = $user->admin;
            return $admin && $warga->rt_rw_id === $admin->rt_rw_id;
        }
        return false;
    }
}
