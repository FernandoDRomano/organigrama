<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class OrganizationContainsValidId implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $organization;

    public function __construct($organization)
    {
        $this->organization = $organization;
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
        return $this->organization->id == $value;
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
