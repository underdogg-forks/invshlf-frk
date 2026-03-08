<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class InvoicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasPermissionTo('view-invoice')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->hasPermissionTo('view-invoice') && $user->hasCompany($invoice->company_id)) {
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
        if ($user->hasPermissionTo('create-invoice')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update(User $user, Invoice $invoice): bool
    {
        if ($user->hasPermissionTo('edit-invoice') && $user->hasCompany($invoice->company_id)) {
            return $invoice->allow_edit;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        if ($user->hasPermissionTo('delete-invoice') && $user->hasCompany($invoice->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return mixed
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        if ($user->hasPermissionTo('delete-invoice') && $user->hasCompany($invoice->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        if ($user->hasPermissionTo('delete-invoice') && $user->hasCompany($invoice->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can send email of the model.
     *
     * @param  \App\Models\Payment  $payment
     * @return mixed
     */
    public function send(User $user, Invoice $invoice): bool
    {
        if ($user->hasPermissionTo('send-invoice') && $user->hasCompany($invoice->company_id)) {
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
        if ($user->hasPermissionTo('delete-invoice')) {
            return true;
        }

        return false;
    }
}
