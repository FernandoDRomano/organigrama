<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\Job;
use App\Models\Obligation;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ObligationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Organization $organization, Department $department, Job $job)
    {
        return $user->id === $organization->user_id
               &&
               $organization->id === $department->organization_id
               && 
               $department->id === $job->department_id;
    }

    public function view(User $user, Obligation $obligation, Organization $organization, Department $department, Job $job)
    {
        return $user->id === $organization->user_id
               &&
               $organization->id === $department->organization_id
               && 
               $department->id === $job->department_id
               && 
               $job->id === $obligation->job_id;
    }

    public function create(User $user, Organization $organization, Department $department, Job $job)
    {
        return $user->id === $organization->user_id
               &&
               $organization->id === $department->organization_id
               && 
               $department->id === $job->department_id;
    }

    public function update(User $user, Obligation $obligation, Organization $organization, Department $department, Job $job)
    {
        return $user->id === $organization->user_id
               &&
               $organization->id === $department->organization_id
               && 
               $department->id === $job->department_id
               && 
               $job->id === $obligation->job_id;
    }

    public function delete(User $user, Obligation $obligation, Organization $organization, Department $department, Job $job)
    {
        return $user->id === $organization->user_id
               &&
               $organization->id === $department->organization_id
               && 
               $department->id === $job->department_id
               && 
               $job->id === $obligation->job_id;
    }

}
