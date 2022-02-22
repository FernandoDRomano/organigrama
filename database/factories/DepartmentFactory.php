<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\DepartmentLevel;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name" => $this->faker->numerify('Department-##'),
            // "organization_id" => $this->faker->numberBetween(1, 9),
            // "department_level_id" => $this->faker->numberBetween(1, 5),
            // "department_id" => $this->faker->numberBetween(1, 10)
            "organization_id" => Organization::factory(),
            "department_level_id" => DepartmentLevel::factory(),
            "department_id" => null
        ];
    }
}
