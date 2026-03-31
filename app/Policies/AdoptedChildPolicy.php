<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AdoptedChild;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdoptedChildPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AdoptedChild');
    }

    public function view(AuthUser $authUser, AdoptedChild $adoptedChild): bool
    {
        return $authUser->can('View:AdoptedChild');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AdoptedChild');
    }

    public function update(AuthUser $authUser, AdoptedChild $adoptedChild): bool
    {
        return $authUser->can('Update:AdoptedChild');
    }

    public function delete(AuthUser $authUser, AdoptedChild $adoptedChild): bool
    {
        return $authUser->can('Delete:AdoptedChild');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AdoptedChild');
    }

    public function restore(AuthUser $authUser, AdoptedChild $adoptedChild): bool
    {
        return $authUser->can('Restore:AdoptedChild');
    }

    public function forceDelete(AuthUser $authUser, AdoptedChild $adoptedChild): bool
    {
        return $authUser->can('ForceDelete:AdoptedChild');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AdoptedChild');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AdoptedChild');
    }

    public function replicate(AuthUser $authUser, AdoptedChild $adoptedChild): bool
    {
        return $authUser->can('Replicate:AdoptedChild');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AdoptedChild');
    }

}