<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LaporanMingguan;
use Illuminate\Auth\Access\HandlesAuthorization;

class LaporanMingguanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LaporanMingguanResource');
    }

    public function view(AuthUser $authUser, LaporanMingguan $laporanMingguan): bool
    {
        return $authUser->can('View:LaporanMingguanResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LaporanMingguanResource');
    }

    public function update(AuthUser $authUser, LaporanMingguan $laporanMingguan): bool
    {
        return $authUser->can('Update:LaporanMingguanResource');
    }

    public function delete(AuthUser $authUser, LaporanMingguan $laporanMingguan): bool
    {
        return $authUser->can('Delete:LaporanMingguanResource');
    }

    public function restore(AuthUser $authUser, LaporanMingguan $laporanMingguan): bool
    {
        return $authUser->can('Restore:LaporanMingguanResource');
    }

    public function forceDelete(AuthUser $authUser, LaporanMingguan $laporanMingguan): bool
    {
        return $authUser->can('ForceDelete:LaporanMingguanResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LaporanMingguanResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LaporanMingguanResource');
    }

    public function replicate(AuthUser $authUser, LaporanMingguan $laporanMingguan): bool
    {
        return $authUser->can('Replicate:LaporanMingguanResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LaporanMingguanResource');
    }

}