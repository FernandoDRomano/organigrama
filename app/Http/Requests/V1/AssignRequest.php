<?php

namespace App\Http\Requests\V1;

use App\Rules\EmployeHasThisJob;
use App\Rules\ExistsEmployeInOrganization;
use App\Rules\ExistsJobInOrganization;
use App\Rules\OrganizationContainsValidId;
use Illuminate\Foundation\Http\FormRequest;

class AssignRequest extends FormRequest
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
        $method = $this->method();

        return [
            "organization_id" => [
                "bail",
                "required",
                "exists:organizations,id",
                new OrganizationContainsValidId($organization)
            ],
            "employe_id" => [
                "bail",
                "required",
                "exists:employes,id",
                new ExistsEmployeInOrganization($organization),
                new EmployeHasThisJob($method)
            ],
            "job_id" => [
                "bail",
                "required",
                "exists:jobs,id",
                new ExistsJobInOrganization($organization)
            ]
        ];
    }
}
