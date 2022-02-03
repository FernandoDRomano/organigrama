<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class JobLevelJustContainOneJobForLevel implements Rule
{
    protected $department;
    protected $job;
    protected $count;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($department, $job = null)
    {
        $this->department = $department;
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
        if ($this->job && $this->job->job_level_id == $value) {
            return true;
        }

        $this->count = $this->department->jobs()->where('job_level_id', $value)->count();
        
        if ($this->count < 1) {
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
        return 'The :attribute contains more one job in this level';
    }
}
