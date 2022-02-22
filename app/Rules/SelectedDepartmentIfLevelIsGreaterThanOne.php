<?php

namespace App\Rules;

use App\Models\Department;
use App\Models\DepartmentLevel;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;

use function PHPUnit\Framework\isNull;

class SelectedDepartmentIfLevelIsGreaterThanOne implements Rule, DataAwareRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $level = DepartmentLevel::FindOrFail($this->data['department_level_id']);
        //si el nivel es el primero, no se debe seleccionar un departamento dependiente por lo tanto pasa la validación
        if ($level->hierarchy == '1') {
            return true;
        }

        $levelNumber = intval($level->hierarchy) - 1;
        $departmentLevel = DepartmentLevel::where('hierarchy', $levelNumber )->first();

        $department = $departmentLevel->departments()->where('organization_id', $this->data['organization_id'])->where('id', $value)->first();

        if ($department) {
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
        return 'The :attribute is invalid, because is not contains in the departments of level higher';
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

}
