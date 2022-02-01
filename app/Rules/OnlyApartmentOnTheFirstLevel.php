<?php

namespace App\Rules;

use App\Models\DepartmentLevel;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;


class OnlyApartmentOnTheFirstLevel implements Rule, DataAwareRule
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
        $level = DepartmentLevel::findOrFail($value);
        
        if($level->hierarchy == '1'){
            
            $count = $level->departments->where('organization_id', '=', $this->data['organization_id'])->count();
            
            if ($count > 0) {
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
