<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\Job;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class JobPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Organization $organization, Department $department)
    {
        return $user->id === $organization->user_id
               &&
               $organization->id === $department->organization_id;
    }

    public function view(User $user, Job $job, Organization $organization, Department $department)
    {
        return $user->id === $organization->user_id
               &&
               $organization->id === $department->organization_id
               &&
               $department->id === $job->department_id;
    }

    public function create(User $user, Organization $organization, Department $department)
    {
        return $user->id === $organization->user_id
               &&
               $organization->id === $department->organization_id;
    }

    public function update(User $user, Job $job, Organization $organization, Department $department)
    {
        return $user->id === $organization->user_id
               &&
               $organization->id === $department->organization_id
               &&
               $department->id === $job->department_id;
    }

    public function delete(User $user, Job $job, Organization $organization, Department $department)
    {
        return $user->id === $organization->user_id
               &&
               $organization->id === $department->organization_id
               &&
               $department->id === $job->department_id;
    }

}
