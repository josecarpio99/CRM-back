<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $customer): bool
    {
        // if ($user->hasRole(RoleEnum::Admin->value)) {
        //     $userBranchIds = User::branch($user->branch)->get()->pluck('id')->toArray();

        //     if (in_array($customer->owner_id, $userBranchIds)) {
        //         return true;
        //     }
        // }

        $usersId = $user->getAssignedUsersIdByRole();

        if (in_array($customer->owner_id, $usersId)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $customer): bool
    {
        $usersId = $user->getAssignedUsersIdByRole();

        if (in_array($customer->owner_id, $usersId)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): bool
    {
        $usersId = $user->getAssignedUsersIdByRole();

        if (in_array($customer->owner_id, $usersId)) {
            return true;
        }

        return false;
    }

}
