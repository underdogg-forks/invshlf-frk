<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class PaymentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasPermissionTo('view-payment')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(User $user, Payment $payment): bool
    {
        if ($user->hasPermissionTo('view-payment') && $user->hasCompany($payment->company_id)) {
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
        if ($user->hasPermissionTo('create-payment')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update(User $user, Payment $payment): bool
    {
        if ($user->hasPermissionTo('edit-payment') && $user->hasCompany($payment->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete(User $user, Payment $payment): bool
    {
        if ($user->hasPermissionTo('delete-payment') && $user->hasCompany($payment->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return mixed
     */
    public function restore(User $user, Payment $payment): bool
    {
        if ($user->hasPermissionTo('delete-payment') && $user->hasCompany($payment->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Payment $payment): bool
    {
        if ($user->hasPermissionTo('delete-payment') && $user->hasCompany($payment->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can send email of the model.
     *
     * @return mixed
     */
    public function send(User $user, Payment $payment): bool
    {
        if ($user->hasPermissionTo('send-payment') && $user->hasCompany($payment->company_id)) {
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
        if ($user->hasPermissionTo('delete-payment')) {
            return true;
        }

        return false;
    }
}
