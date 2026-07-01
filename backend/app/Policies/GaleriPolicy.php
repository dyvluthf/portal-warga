<?php

namespace App\Policies;

use App\Models\GaleriAlbum;
use App\Models\User;

class GaleriPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GaleriAlbum $galeriAlbum): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }

    public function delete(User $user, GaleriAlbum $galeriAlbum): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }
}
