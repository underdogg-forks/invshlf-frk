<?php

namespace App\Policies;

use App\Models\CustomField;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class CustomFieldPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-custom-field');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(User $user, CustomField $customField): bool
    {
        return $user->hasPermissionTo('view-custom-field') && $user->hasCompany($customField->company_id);
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-custom-field');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update(User $user, CustomField $customField): bool
    {
        return $user->hasPermissionTo('edit-custom-field') && $user->hasCompany($customField->company_id);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete(User $user, CustomField $customField): bool
    {
        return $user->hasPermissionTo('delete-custom-field') && $user->hasCompany($customField->company_id);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return mixed
     */
    public function restore(User $user, CustomField $customField): bool
    {
        return $user->hasPermissionTo('delete-custom-field') && $user->hasCompany($customField->company_id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return mixed
     */
    public function forceDelete(User $user, CustomField $customField): bool
    {
        return $user->hasPermissionTo('delete-custom-field') && $user->hasCompany($customField->company_id);
    }
}
