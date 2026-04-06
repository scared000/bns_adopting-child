<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OfficeChildAssign;
use Illuminate\Auth\Access\HandlesAuthorization;

class OfficeChildAssignPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OfficeChildAssign');
    }

    public function view(AuthUser $authUser, OfficeChildAssign $officeChildAssign): bool
    {
        return $authUser->can('View:OfficeChildAssign');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OfficeChildAssign');
    }

    public function update(AuthUser $authUser, OfficeChildAssign $officeChildAssign): bool
    {
        return $authUser->can('Update:OfficeChildAssign');
    }

    public function delete(AuthUser $authUser, OfficeChildAssign $officeChildAssign): bool
    {
        return $authUser->can('Delete:OfficeChildAssign');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:OfficeChildAssign');
    }

    public function restore(AuthUser $authUser, OfficeChildAssign $officeChildAssign): bool
    {
        return $authUser->can('Restore:OfficeChildAssign');
    }

    public function forceDelete(AuthUser $authUser, OfficeChildAssign $officeChildAssign): bool
    {
        return $authUser->can('ForceDelete:OfficeChildAssign');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OfficeChildAssign');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OfficeChildAssign');
    }

    public function replicate(AuthUser $authUser, OfficeChildAssign $officeChildAssign): bool
    {
        return $authUser->can('Replicate:OfficeChildAssign');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OfficeChildAssign');
    }

}