<?php

namespace App\Http\Requests\V1;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class JobLevelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $job_level = $this->route()->parameter('job_level');
        return [
            "name" => "required|min:2|max:20",
            "hierarchy" => [
                "required",
                "integer",
                "min:1",
                "max:10",
                Rule::unique('job_levels')->ignore($job_level),
            ]
        ];
    }
}
