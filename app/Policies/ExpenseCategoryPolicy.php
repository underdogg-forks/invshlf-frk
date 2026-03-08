<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class ExpenseCategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-expense');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(User $user, ExpenseCategory $expenseCategory): bool
    {
        return $user->hasPermissionTo('view-expense') && $user->hasCompany($expenseCategory->company_id);
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-expense');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update(User $user, ExpenseCategory $expenseCategory): bool
    {
        return $user->hasPermissionTo('edit-expense') && $user->hasCompany($expenseCategory->company_id);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete(User $user, ExpenseCategory $expenseCategory): bool
    {
        return $user->hasPermissionTo('delete-expense') && $user->hasCompany($expenseCategory->company_id);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return mixed
     */
    public function restore(User $user, ExpenseCategory $expenseCategory): bool
    {
        return $user->hasPermissionTo('delete-expense') && $user->hasCompany($expenseCategory->company_id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return mixed
     */
    public function forceDelete(User $user, ExpenseCategory $expenseCategory): bool
    {
        return $user->hasPermissionTo('delete-expense') && $user->hasCompany($expenseCategory->company_id);
    }
}
