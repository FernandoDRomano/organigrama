<?php

namespace App\Providers;

use App\Models\Department;
use App\Models\Employe;
use App\Models\Organization;
use App\Policies\DepartmentPolicy;
use App\Policies\EmployePolicy;
use App\Policies\JobPolicy;
use App\Policies\OrganizationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Job::class => JobPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
