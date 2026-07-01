<?php

namespace App\Policies;

use App\Models\Agenda;
use App\Models\User;

class AgendaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Agenda $agenda): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }

    public function update(User $user, Agenda $agenda): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }

    public function delete(User $user, Agenda $agenda): bool
    {
        return in_array($user->role, ['super_admin', 'admin_rt_rw']);
    }
}
