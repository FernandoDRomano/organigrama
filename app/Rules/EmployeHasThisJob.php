<?php

namespace App\Rules;

use App\Models\Employe;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;

class EmployeHasThisJob implements Rule, DataAwareRule
{
    protected $method;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(String $method)
    {
        $this->method = $method;
    }

    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
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
        if($this->method == 'DELETE'){
            return true;
        }
        
        return Employe::find($value)->jobs()->where('jobs.id', $this->data['job_id'])->doesntExist();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute has this job id';
    }
}
