<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class DashboardPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Company $company): bool
    {
        if ($user->can('dashboard') && $user->hasCompany($company->id)) {
            return true;
        }

        return false;
    }
}
