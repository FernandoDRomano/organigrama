<?php

namespace App\Rules;

use App\Models\Job;
use Illuminate\Contracts\Validation\Rule;

class JobContainValidId implements Rule
{
    protected $job;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Job $job = null)
    {
        $this->job = $job;
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
        return $this->job->id == $value;
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
