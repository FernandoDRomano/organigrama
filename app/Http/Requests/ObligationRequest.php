<?php

namespace App\Http\Requests;

use App\Rules\JobContainValidId;
use App\Rules\ValidateJobInDepartmentInOrganization;
use Illuminate\Foundation\Http\FormRequest;

class ObligationRequest extends FormRequest
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
            "description" => "required|min:3",
            "job_id" => [
                "bail",
                "required",
                "exists:jobs,id",
                new JobContainValidId($job),
                new ValidateJobInDepartmentInOrganization($organization, $department, $job)
            ]
        ];
    }
}
