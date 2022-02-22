<?php

namespace App\Rules;

use App\Models\Organization;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;

use function PHPUnit\Framework\isNull;

class ExitsDepartmentInOrganization implements Rule, DataAwareRule
{
    protected $organization;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($organization)
    {
        $this->organization = $organization;
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
        $collect = collect($this->organization->departments);

        if ($collect->contains('id', $value)) {
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
        return 'The :attribute is invalid, because :attribute is not exists in organization';
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
