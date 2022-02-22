<?php

namespace App\Rules;

use App\Models\Department;
use App\Models\DepartmentLevel;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;

use function PHPUnit\Framework\isNull;

class OnlyApartmentOnTheFirstLevel implements Rule, DataAwareRule
{
    private $department;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Department $department = null)
    {
        $this->department = $department;
    }

    /*
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
        $level = DepartmentLevel::findOrFail($value);
        
        if($level->hierarchy == '1'){
            
            $departments = collect( $level->departments()->where('organization_id', '=', $this->data['organization_id'])->get() );

            if($departments->count() > 0){

                if(isset($this->department)){
                    
                    if(isset($this->department) && $departments->contains($this->department)){
                        
                        return true;
                    }

                }

                return false;
            }

        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is invalid because exists one department in the first level';
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
