<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Immunizations;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImmunizationsPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Immunizations');
    }

    public function view(AuthUser $authUser, Immunizations $immunizations): bool
    {
        return $authUser->can('View:Immunizations');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Immunizations');
    }

    public function update(AuthUser $authUser, Immunizations $immunizations): bool
    {
        return $authUser->can('Update:Immunizations');
    }

    public function delete(AuthUser $authUser, Immunizations $immunizations): bool
    {
        return $authUser->can('Delete:Immunizations');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Immunizations');
    }

    public function restore(AuthUser $authUser, Immunizations $immunizations): bool
    {
        return $authUser->can('Restore:Immunizations');
    }

    public function forceDelete(AuthUser $authUser, Immunizations $immunizations): bool
    {
        return $authUser->can('ForceDelete:Immunizations');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Immunizations');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Immunizations');
    }

    public function replicate(AuthUser $authUser, Immunizations $immunizations): bool
    {
        return $authUser->can('Replicate:Immunizations');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Immunizations');
    }

}
