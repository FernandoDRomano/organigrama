<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Organization $organization)
    {
        return $user->id === $organization->user_id;
    }

    public function view(User $user, Department $department, Organization $organization)
    {
        return $user->id === $organization->user_id
               &&
               $organization->id === $department->organization_id;
    }

    public function create(User $user, Organization $organization)
    {
        return $user->id === $organization->user_id;
    }

    public function update(User $user, Department $department, Organization $organization)
    {
        return $user->id === $organization->user_id
               &&
               $organization->id === $department->organization_id;
    }

    public function delete(User $user, Department $department, Organization $organization)
    {
        return $user->id === $organization->user_id
               &&
               $organization->id === $department->organization_id;
    }

}
