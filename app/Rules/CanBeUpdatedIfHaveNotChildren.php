<?php

namespace App\Rules;

use App\Models\Department;
use Illuminate\Contracts\Validation\Rule;

class CanBeUpdatedIfHaveNotChildren implements Rule
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

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //Si no existe la variable department es que estoy haciendo un post en vez de un put, y por lo tanto pasa la validación
        if(isset($this->department)){
            
            //Al no haber modificaciones en el department_level_id pasa la validación
            if($this->department->level->id == $value){
                return true;
            }

            //Si se modifico y tiene departamentos dependientes falla
            if($this->department->departments()->count() > 0){
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
        return 'The :attribute is invalid because exists more than departments children';
    }
}
