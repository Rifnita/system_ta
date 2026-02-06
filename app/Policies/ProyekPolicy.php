<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Proyek;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProyekPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Proyek');
    }

    public function view(AuthUser $authUser, Proyek $proyek): bool
    {
        return $authUser->can('View:Proyek');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Proyek');
    }

    public function update(AuthUser $authUser, Proyek $proyek): bool
    {
        return $authUser->can('Update:Proyek');
    }

    public function delete(AuthUser $authUser, Proyek $proyek): bool
    {
        return $authUser->can('Delete:Proyek');
    }

    public function restore(AuthUser $authUser, Proyek $proyek): bool
    {
        return $authUser->can('Restore:Proyek');
    }

    public function forceDelete(AuthUser $authUser, Proyek $proyek): bool
    {
        return $authUser->can('ForceDelete:Proyek');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Proyek');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Proyek');
    }

    public function replicate(AuthUser $authUser, Proyek $proyek): bool
    {
        return $authUser->can('Replicate:Proyek');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Proyek');
    }

}