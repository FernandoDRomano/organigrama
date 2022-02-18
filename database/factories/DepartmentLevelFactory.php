<?php

namespace Database\Factories;

use App\Models\DepartmentLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentLevelFactory extends Factory
{
    protected $model = DepartmentLevel::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name" => $this->faker->numerify('Department Level - #'),
            "hierarchy" => $this->faker->numberBetween(1, 10)
        ];
    }
}
