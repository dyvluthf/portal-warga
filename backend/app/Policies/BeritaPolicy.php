<?php

namespace App\Policies;

use App\Models\Berita;
use App\Models\User;

class BeritaPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }

    public function update(User $user, Berita $berita): bool
    {
        return $user->role === 'super_admin' || $berita->penulis_id === $user->id;
    }

    public function delete(User $user, Berita $berita): bool
    {
        return $user->role === 'super_admin' || $berita->penulis_id === $user->id;
    }
}
