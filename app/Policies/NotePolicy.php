<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class NotePolicy
{
    use HandlesAuthorization;

    public function manageNotes(User $user): bool
    {
        if ($user->hasPermissionTo('manage-all-notes')) {
            return true;
        }

        return false;
    }

    public function viewNotes(User $user): bool
    {
        if ($user->hasPermissionTo('view-all-notes')) {
            return true;
        }

        return false;
    }
}
