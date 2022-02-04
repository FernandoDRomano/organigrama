<?php

namespace App\Rules;

use App\Models\Department;
use App\Models\Job;
use App\Models\Organization;
use Illuminate\Contracts\Validation\Rule;

class ValidateJobInDepartmentInOrganization implements Rule
{
    protected $organization;
    protected $department;
    protected $job;

    public function __construct(Organization $organization = null, Department $department = null, Job $job = null)
    {
        $this->organization = $organization;
        $this->department = $department;
        $this->job = $job;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {        
        if ($this->organization->id === $this->department->organization_id && $this->department->id === $this->job->department_id) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is invalid because is not contains in the department and the organization';
    }
}
