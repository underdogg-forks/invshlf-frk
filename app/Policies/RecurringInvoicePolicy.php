<?php

namespace App\Policies;

use App\Models\RecurringInvoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class RecurringInvoicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasPermissionTo('view-recurring-invoice')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, RecurringInvoice $recurringInvoice): bool
    {
        if ($user->hasPermissionTo('view-recurring-invoice') && $user->hasCompany($recurringInvoice->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user): bool
    {
        if ($user->hasPermissionTo('create-recurring-invoice')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, RecurringInvoice $recurringInvoice): bool
    {
        if ($user->hasPermissionTo('edit-recurring-invoice') && $user->hasCompany($recurringInvoice->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, RecurringInvoice $recurringInvoice): bool
    {
        if ($user->hasPermissionTo('delete-recurring-invoice') && $user->hasCompany($recurringInvoice->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, RecurringInvoice $recurringInvoice): bool
    {
        if ($user->hasPermissionTo('delete-recurring-invoice') && $user->hasCompany($recurringInvoice->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, RecurringInvoice $recurringInvoice): bool
    {
        if ($user->hasPermissionTo('delete-recurring-invoice') && $user->hasCompany($recurringInvoice->company_id)) {
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
        if ($user->hasPermissionTo('delete-recurring-invoice')) {
            return true;
        }

        return false;
    }
}
