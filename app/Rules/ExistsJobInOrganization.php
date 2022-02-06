<?php

namespace App\Rules;

use App\Models\Organization;
use Illuminate\Contracts\Validation\Rule;

class ExistsJobInOrganization implements Rule
{
    protected $organization;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Organization $organization)
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
        return $this->organization->jobs()->where('jobs.id', $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is invalid, because :attribute is not contains in organization';
    }
}
