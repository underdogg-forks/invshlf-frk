<?php

namespace App\Policies;

use App\Models\ExchangeRateProvider;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class ExchangeRateProviderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasPermissionTo('view-exchange-rate-provider')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ExchangeRateProvider $exchangeRateProvider): bool
    {
        if ($user->hasPermissionTo('view-exchange-rate-provider') && $user->hasCompany($exchangeRateProvider->company_id)) {
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
        if ($user->hasPermissionTo('create-exchange-rate-provider')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ExchangeRateProvider $exchangeRateProvider): bool
    {
        if ($user->hasPermissionTo('edit-exchange-rate-provider') && $user->hasCompany($exchangeRateProvider->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ExchangeRateProvider $exchangeRateProvider): bool
    {
        if ($user->hasPermissionTo('delete-exchange-rate-provider') && $user->hasCompany($exchangeRateProvider->company_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ExchangeRateProvider $exchangeRateProvider): bool
    {
        return $user->hasPermissionTo('delete-exchange-rate-provider') && $user->hasCompany($exchangeRateProvider->company_id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ExchangeRateProvider $exchangeRateProvider): bool
    {
        return $user->hasPermissionTo('delete-exchange-rate-provider') && $user->hasCompany($exchangeRateProvider->company_id);
    }
}
