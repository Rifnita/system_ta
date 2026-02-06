<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LaporanAktivitas;
use Illuminate\Auth\Access\HandlesAuthorization;

class LaporanAktivitasPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        // Semua authenticated user bisa view (filtered di query)
        return true;
    }

    public function view(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        // Admin bisa view semua, user biasa hanya bisa view task mereka sendiri
        if ($authUser->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }
        
        return $laporanAktivitas->user_id === $authUser->id;
    }

    public function create(AuthUser $authUser): bool
    {
        // Semua authenticated user bisa create daily task
        return true;
    }

    public function update(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        // Admin bisa update semua, user biasa hanya bisa update task mereka sendiri
        if ($authUser->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }
        
        return $laporanAktivitas->user_id === $authUser->id;
    }

    public function delete(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        // Admin bisa delete semua, user biasa hanya bisa delete task mereka sendiri
        if ($authUser->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }
        
        return $laporanAktivitas->user_id === $authUser->id;
    }

    public function restore(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('Restore:LaporanAktivitas');
    }

    public function forceDelete(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('ForceDelete:LaporanAktivitas');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LaporanAktivitas');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LaporanAktivitas');
    }

    public function replicate(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('Replicate:LaporanAktivitas');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LaporanAktivitas');
    }

}