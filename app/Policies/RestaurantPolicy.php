<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RestaurantPolicy
{
    use HandlesAuthorization;
    /**
     * Determine whether the user can view the model.
     */
    public function permissionRestaurants(User $user): bool
    {
        return $user->id == 1;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole("user");
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Restaurant $restaurant): bool
    {
        return $user->id === $restaurant->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    // public function delete(User $user, Restaurant $restaurant): bool
    // {
    //     //
    // }
}
