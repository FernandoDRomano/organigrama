<?php

namespace App\Policies;

use App\Models\DepartmentLevel;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentLevelPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->role === User::ROLE_ADMIN;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DepartmentLevel  $departmentLevel
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, DepartmentLevel $departmentLevel)
    {
        return $user->role === User::ROLE_ADMIN;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->role === User::ROLE_ADMIN;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DepartmentLevel  $departmentLevel
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, DepartmentLevel $departmentLevel)
    {
        return $user->role === User::ROLE_ADMIN;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DepartmentLevel  $departmentLevel
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, DepartmentLevel $departmentLevel)
    {
        return $user->role === User::ROLE_ADMIN;
    }

}
