<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BaranggayNutritionScholars;
use Illuminate\Auth\Access\HandlesAuthorization;

class BaranggayNutritionScholarsPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BaranggayNutritionScholars');
    }

    public function view(AuthUser $authUser, BaranggayNutritionScholars $baranggayNutritionScholars): bool
    {
        return $authUser->can('View:BaranggayNutritionScholars');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BaranggayNutritionScholars');
    }

    public function update(AuthUser $authUser, BaranggayNutritionScholars $baranggayNutritionScholars): bool
    {
        return $authUser->can('Update:BaranggayNutritionScholars');
    }

    public function delete(AuthUser $authUser, BaranggayNutritionScholars $baranggayNutritionScholars): bool
    {
        return $authUser->can('Delete:BaranggayNutritionScholars');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BaranggayNutritionScholars');
    }

    public function restore(AuthUser $authUser, BaranggayNutritionScholars $baranggayNutritionScholars): bool
    {
        return $authUser->can('Restore:BaranggayNutritionScholars');
    }

    public function forceDelete(AuthUser $authUser, BaranggayNutritionScholars $baranggayNutritionScholars): bool
    {
        return $authUser->can('ForceDelete:BaranggayNutritionScholars');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BaranggayNutritionScholars');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BaranggayNutritionScholars');
    }

    public function replicate(AuthUser $authUser, BaranggayNutritionScholars $baranggayNutritionScholars): bool
    {
        return $authUser->can('Replicate:BaranggayNutritionScholars');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BaranggayNutritionScholars');
    }

}
