<?php

namespace App\Http\Requests;

use App\Rules\OrganizationContainsValidId;
use Illuminate\Foundation\Http\FormRequest;

class EmployeRequest extends FormRequest
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

        return [
            "first_name" => "required|min:2",
            "last_name" => "required|min:2",
            "dni" => "required|max:9|digits_between:7,8",
            "date_of_birth" => "required|date",
            "address" => "required|min:3|max:256",
            "organization_id" => [
                "bail",
                "required", 
                "exists:organizations,id",
                new OrganizationContainsValidId($organization)
            ]
        ];
    }
}
