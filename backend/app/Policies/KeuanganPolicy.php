<?php

namespace App\Policies;

use App\Models\Keuangan;
use App\Models\User;

class KeuanganPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }
}
