<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class ExpensePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasPermissionTo('view-expense')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(User $user, Expense $expense): bool
    {
        if ($user->hasPermissionTo('view-expense') && $user->hasCompany($expense->company_id)) {
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
        if ($user->hasPermissionTo('create-expense')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update(User $user, Expense $expense): bool
    {
        if ($user->hasPermissionTo('edit-expense') && $user->hasCompany($expense->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete(User $user, Expense $expense): bool
    {
        if ($user->hasPermissionTo('delete-expense') && $user->hasCompany($expense->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return mixed
     */
    public function restore(User $user, Expense $expense): bool
    {
        if ($user->hasPermissionTo('delete-expense') && $user->hasCompany($expense->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Expense $expense): bool
    {
        if ($user->hasPermissionTo('delete-expense') && $user->hasCompany($expense->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete models.
     *
     * @return mixed
     */
    public function deleteMultiple(User $user): bool
    {
        if ($user->hasPermissionTo('delete-expense')) {
            return true;
        }

        return false;
    }
}
