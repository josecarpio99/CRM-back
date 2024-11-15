<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DealPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Deal $deal): bool
    {
        $usersId = $user->getAssignedUsersIdByRole();

        if (in_array($deal->owner_id, $usersId)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can convert the model.
     */
    public function convert(User $user, Deal $deal): bool
    {
        $usersId = $user->getAssignedUsersIdByRole();

        if (in_array($deal->owner_id, $usersId)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Deal $deal): bool
    {
        $usersId = $user->getAssignedUsersIdByRole();

        if (in_array($deal->owner_id, $usersId)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Deal $deal): bool
    {
        $usersId = $user->getAssignedUsersIdByRole();

        if (in_array($deal->owner_id, $usersId)) {
            return true;
        }

        return false;
    }

}
