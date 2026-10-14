<?php

namespace App\Policies;

use App\Models\InvestorProfile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvestorProfilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any investor profiles.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' || InvestorProfile::isUserSupervisor($user);
    }

    /**
     * Determine whether the user can view the investor profile.
     */
    public function view(User $user, InvestorProfile $profile): bool
    {
        return $user->role === 'admin' 
            || InvestorProfile::isUserSupervisor($user)
            || $profile->user_id === $user->id;
    }

    /**
     * Determine whether the user can create investor profiles.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin' || InvestorProfile::isUserSupervisor($user);
    }

    /**
     * Internal Policy Enforcement:
     * Before an Admin can edit an Investor Profile, there must be approval from the Supervisor.
     */
    public function update(User $user, InvestorProfile $profile): bool
    {
        return $profile->isEditAuthorized($user);
    }

    /**
     * Determine whether the user can delete the investor profile.
     */
    public function delete(User $user, InvestorProfile $profile): bool
    {
        return InvestorProfile::isUserSupervisor($user);
    }
}
