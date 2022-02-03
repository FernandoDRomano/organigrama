<?php

namespace App\Http\Requests;

use App\Rules\DepartmentContainsValidId;
use App\Rules\ExitsDepartmentInOrganization;
use App\Rules\JobLevelJustContainOneJobForLevel;
use Illuminate\Foundation\Http\FormRequest;

class JobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $organization = $this->route()->parameter('organization');
        $department = $this->route()->parameter('department');
        $job = $this->route()->parameter('job');

        return [
            "name" => "bail|required|min:2",
            "department_id" => [
                "bail",
                "required",
                "exists:departments,id",
                new DepartmentContainsValidId($department),
                new ExitsDepartmentInOrganization($organization)
            ],
            "job_level_id" => [
                "bail",
                "required",
                "exists:job_levels,id",
                new JobLevelJustContainOneJobForLevel($department, $job)
            ]
        ];
    }
}
