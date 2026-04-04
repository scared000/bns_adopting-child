<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OfficeChildVisit;
use Illuminate\Auth\Access\HandlesAuthorization;

class OfficeChildVisitPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OfficeChildVisit');
    }

    public function view(AuthUser $authUser, OfficeChildVisit $officeChildVisit): bool
    {
        return $authUser->can('View:OfficeChildVisit');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OfficeChildVisit');
    }

    public function update(AuthUser $authUser, OfficeChildVisit $officeChildVisit): bool
    {
        return $authUser->can('Update:OfficeChildVisit');
    }

    public function delete(AuthUser $authUser, OfficeChildVisit $officeChildVisit): bool
    {
        return $authUser->can('Delete:OfficeChildVisit');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:OfficeChildVisit');
    }

    public function restore(AuthUser $authUser, OfficeChildVisit $officeChildVisit): bool
    {
        return $authUser->can('Restore:OfficeChildVisit');
    }

    public function forceDelete(AuthUser $authUser, OfficeChildVisit $officeChildVisit): bool
    {
        return $authUser->can('ForceDelete:OfficeChildVisit');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OfficeChildVisit');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OfficeChildVisit');
    }

    public function replicate(AuthUser $authUser, OfficeChildVisit $officeChildVisit): bool
    {
        return $authUser->can('Replicate:OfficeChildVisit');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OfficeChildVisit');
    }

}
