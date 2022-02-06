<?php

namespace App\Policies;

use App\Models\Employe;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use phpDocumentor\Reflection\Types\Boolean;

class EmployePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Organization $organization)
    {
        return $user->id === $organization->user_id;
    }

    public function view(User $user, Employe $employe, Organization $organization)
    {
       return $user->id === $organization->user_id 
              && 
              $organization->id === $employe->organization_id;
    }

    public function create(User $user, Organization $organization)
    {
        return $user->id === $organization->user_id;
    }

    public function update(User $user, Employe $employe, Organization $organization)
    {
        return $user->id === $organization->user_id
               && 
               $organization->id === $employe->organization_id;
    }

    public function delete(User $user, Employe $employe, Organization $organization)
    {
        return $user->id === $organization->user_id
               &&
               $organization->id === $employe->organization_id;
    }

}
