<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DepartmentContainsValidId implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $department;

    public function __construct($department)
    {
        $this->department = $department;
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
        return $this->department->id == $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is invalid because :attribute does not correspont with url';
    }
}
