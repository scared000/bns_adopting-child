<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BnsProfile;
use Illuminate\Auth\Access\HandlesAuthorization;

class BnsProfilePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BnsProfile');
    }

    public function view(AuthUser $authUser, BnsProfile $bnsProfile): bool
    {
        return $authUser->can('View:BnsProfile');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BnsProfile');
    }

    public function update(AuthUser $authUser, BnsProfile $bnsProfile): bool
    {
        return $authUser->can('Update:BnsProfile');
    }

    public function delete(AuthUser $authUser, BnsProfile $bnsProfile): bool
    {
        return $authUser->can('Delete:BnsProfile');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BnsProfile');
    }

    public function restore(AuthUser $authUser, BnsProfile $bnsProfile): bool
    {
        return $authUser->can('Restore:BnsProfile');
    }

    public function forceDelete(AuthUser $authUser, BnsProfile $bnsProfile): bool
    {
        return $authUser->can('ForceDelete:BnsProfile');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BnsProfile');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BnsProfile');
    }

    public function replicate(AuthUser $authUser, BnsProfile $bnsProfile): bool
    {
        return $authUser->can('Replicate:BnsProfile');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BnsProfile');
    }

}