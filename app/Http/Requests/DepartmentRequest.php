<?php

namespace App\Http\Requests;

use App\Models\DepartmentLevel;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExitsDepartmentInOrganization;
use App\Rules\OnlyApartmentOnTheFirstLevel;
use App\Rules\OrganizationContainsValidId;
use App\Rules\SelectedDepartmentIfLevelIsGreaterThanOne;

class DepartmentRequest extends FormRequest
{

    protected $organization;
    protected $hierarchy;

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
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    public function initValues(){
        $this->organization = $this->route()->parameter('organization');

        if ($this->department_level_id) {
            $aux = DepartmentLevel::where('id', $this->department_level_id)->first();
            if($aux){
                $this->hierarchy = $aux->hierarchy;
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->initValues();
        return [
            "name" => "required|min:3",
            "organization_id" => [
                "bail",
                "required",
                "exists:organizations,id",
                new OrganizationContainsValidId($this->organization)
            ],
            "department_level_id" => [
                "bail",
                "required",
                "exists:department_levels,id",
                new OnlyApartmentOnTheFirstLevel
            ],
            //ASIGNO EL VALOR  DE hierarchy A department_level_id PORQUE NO LO TOMA COMO VARIABLE {$this->hierarchy}
            $this->department_level_id = $this->hierarchy,
            "department_id" => [
                "bail",
                "required_unless:department_level_id,!=,1",
                "nullable",
                new ExitsDepartmentInOrganization($this->organization),
                new SelectedDepartmentIfLevelIsGreaterThanOne
            ]            
        ];
    }

    public function messages()
    {
        return [
            "department_id.required_unless" => "The :attribute is required because hierarchy is greater than one"
        ];
    }


}
