<?php

namespace App\Policies;

use App\Models\User;

class RtRwPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }

    public function view(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }

    public function create(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    public function update(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    public function delete(User $user): bool
    {
        return $user->role === 'super_admin';
    }
}
