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
        if ($user->can('view-custom-field', CustomField::class)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(User $user, CustomField $customField): bool
    {
        if ($user->can('view-custom-field', $customField) && $user->hasCompany($customField->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create(User $user): bool
    {
        if ($user->can('create-custom-field', CustomField::class)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update(User $user, CustomField $customField): bool
    {
        if ($user->can('edit-custom-field', $customField) && $user->hasCompany($customField->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete(User $user, CustomField $customField): bool
    {
        if ($user->can('delete-custom-field', $customField) && $user->hasCompany($customField->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return mixed
     */
    public function restore(User $user, CustomField $customField): bool
    {
        if ($user->can('delete-custom-field', $customField) && $user->hasCompany($customField->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return mixed
     */
    public function forceDelete(User $user, CustomField $customField): bool
    {
        if ($user->can('delete-custom-field', $customField) && $user->hasCompany($customField->company_id)) {
            return true;
        }

        return false;
    }
}
