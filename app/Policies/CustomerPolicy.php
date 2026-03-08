<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class CustomerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-customer');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo('view-customer') && $user->hasCompany($customer->company_id);
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-customer');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo('edit-customer') && $user->hasCompany($customer->company_id);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo('delete-customer') && $user->hasCompany($customer->company_id);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return mixed
     */
    public function restore(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo('delete-customer') && $user->hasCompany($customer->company_id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo('delete-customer') && $user->hasCompany($customer->company_id);
    }

    /**
     * Determine whether the user can delete models.
     *
     * @return mixed
     */
    public function deleteMultiple(User $user)
    {
        return $user->hasPermissionTo('delete-customer');
    }
}
