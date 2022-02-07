<?php

namespace App\Policies;

use App\Models\Assign;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssignPolicy
{
    use HandlesAuthorization;

    public function create(User $user, Organization $organization)
    {
        return $user->id === $organization->user_id;
    }

    public function delete(User $user, Assign $assign, Organization $organization)
    {
        return $user->id === $organization->user_id;
    }
}
