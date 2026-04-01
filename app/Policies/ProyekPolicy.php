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
        return $authUser->can('ViewAny:ProyekResource');
    }

    public function view(AuthUser $authUser, Proyek $proyek): bool
    {
        return $authUser->can('View:ProyekResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProyekResource');
    }

    public function update(AuthUser $authUser, Proyek $proyek): bool
    {
        return $authUser->can('Update:ProyekResource');
    }

    public function delete(AuthUser $authUser, Proyek $proyek): bool
    {
        return $authUser->can('Delete:ProyekResource');
    }

    public function restore(AuthUser $authUser, Proyek $proyek): bool
    {
        return $authUser->can('Restore:ProyekResource');
    }

    public function forceDelete(AuthUser $authUser, Proyek $proyek): bool
    {
        return $authUser->can('ForceDelete:ProyekResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProyekResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProyekResource');
    }

    public function replicate(AuthUser $authUser, Proyek $proyek): bool
    {
        return $authUser->can('Replicate:ProyekResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProyekResource');
    }

}