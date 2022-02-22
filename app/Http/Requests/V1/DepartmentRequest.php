<?php

namespace App\Http\Requests\V1;

use App\Models\DepartmentLevel;
use App\Rules\CanBeUpdatedIfHaveNotChildren;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExitsDepartmentInOrganization;
use App\Rules\OnlyApartmentOnTheFirstLevel;
use App\Rules\OrganizationContainsValidId;
use App\Rules\SelectedDepartmentIfLevelIsGreaterThanOne;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
{

    protected $organization;
    protected $department;

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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() :array
    {
        $this->initValues();
        return [
            "name" => "required|min:3|max:30",
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
                new OnlyApartmentOnTheFirstLevel($this->department),
                new CanBeUpdatedIfHaveNotChildren($this->department)
            ],
            "department_id" => [
                "bail",
                Rule::requiredIf( $this->hierarchyMoreThanOne() ),
                "nullable",
                new ExitsDepartmentInOrganization($this->organization),
                new SelectedDepartmentIfLevelIsGreaterThanOne
            ]            
        ];
    }

    public function messages() :array
    {
        return [
            "department_id.required_if" => "The :attribute is required because hierarchy is greater than one"
        ];
    }

    public function initValues() :void
    {
        $this->organization = $this->route()->parameter('organization');

        if($this->route()->parameter('department')){
            $this->department = $this->route()->parameter('department');
        }
    }

    public function hierarchyMoreThanOne() :bool
    {
        if ($this->department_level_id) {
            $level = DepartmentLevel::where('id', $this->department_level_id)->first();
            
            return ($level && $level->hierarchy > 1);
        }

        return true;
    }

}
