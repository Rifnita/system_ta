<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PengajuanCuti;
use Illuminate\Foundation\Auth\User as AuthUser;

class PengajuanCutiPolicy
{
    private function isAdmin(AuthUser $authUser): bool
    {
        return method_exists($authUser, 'hasRole')
            && ($authUser->hasRole('super_admin') || $authUser->hasRole('admin'));
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return (bool) $authUser;
    }

    public function view(AuthUser $authUser, PengajuanCuti $pengajuanCuti): bool
    {
        return $this->isAdmin($authUser) || (int) $pengajuanCuti->user_id === (int) $authUser->id;
    }

    public function create(AuthUser $authUser): bool
    {
        return (bool) $authUser;
    }

    public function update(AuthUser $authUser, PengajuanCuti $pengajuanCuti): bool
    {
        if ($this->isAdmin($authUser)) {
            return true;
        }

        return (int) $pengajuanCuti->user_id === (int) $authUser->id
            && $pengajuanCuti->status_pengajuan === 'menunggu';
    }

    public function delete(AuthUser $authUser, PengajuanCuti $pengajuanCuti): bool
    {
        if ($this->isAdmin($authUser)) {
            return true;
        }

        return (int) $pengajuanCuti->user_id === (int) $authUser->id
            && $pengajuanCuti->status_pengajuan === 'menunggu';
    }

    public function restore(AuthUser $authUser, PengajuanCuti $pengajuanCuti): bool
    {
        return $this->isAdmin($authUser);
    }

    public function forceDelete(AuthUser $authUser, PengajuanCuti $pengajuanCuti): bool
    {
        return $this->isAdmin($authUser);
    }

    public function approve(AuthUser $authUser, PengajuanCuti $pengajuanCuti): bool
    {
        return $this->isAdmin($authUser) && $pengajuanCuti->status_pengajuan === 'menunggu';
    }

    public function reject(AuthUser $authUser, PengajuanCuti $pengajuanCuti): bool
    {
        return $this->isAdmin($authUser) && $pengajuanCuti->status_pengajuan === 'menunggu';
    }
}
