<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Assign;
use App\Models\Employe;
use App\Models\Department;
use App\Models\Obligation;
use App\Policies\JobPolicy;
use App\Models\Organization;
use App\Policies\AssignPolicy;
use App\Policies\EmployePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\ObligationPolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Organization::class => OrganizationPolicy::class,
        Employe::class => EmployePolicy::class,
        Department::class => DepartmentPolicy::class,
        Job::class => JobPolicy::class,
        Obligation::class => ObligationPolicy::class,
        Assign::class => AssignPolicy::class,
        User::class => UserPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('view-organization-chart', function (User $user, Organization $organization) {
            return $user->id === $organization->user_id;
        });

        Gate::define('update-status-user', function (User $user) {
            return  $user->role === 'admin';
        });
    }
}
