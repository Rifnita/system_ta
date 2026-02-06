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
        return $authUser->can('ViewAny:LaporanMingguan');
    }

    public function view(AuthUser $authUser, LaporanMingguan $laporanMingguan): bool
    {
        return $authUser->can('View:LaporanMingguan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LaporanMingguan');
    }

    public function update(AuthUser $authUser, LaporanMingguan $laporanMingguan): bool
    {
        return $authUser->can('Update:LaporanMingguan');
    }

    public function delete(AuthUser $authUser, LaporanMingguan $laporanMingguan): bool
    {
        return $authUser->can('Delete:LaporanMingguan');
    }

    public function restore(AuthUser $authUser, LaporanMingguan $laporanMingguan): bool
    {
        return $authUser->can('Restore:LaporanMingguan');
    }

    public function forceDelete(AuthUser $authUser, LaporanMingguan $laporanMingguan): bool
    {
        return $authUser->can('ForceDelete:LaporanMingguan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LaporanMingguan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LaporanMingguan');
    }

    public function replicate(AuthUser $authUser, LaporanMingguan $laporanMingguan): bool
    {
        return $authUser->can('Replicate:LaporanMingguan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LaporanMingguan');
    }

}