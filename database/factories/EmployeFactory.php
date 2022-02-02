<?php

namespace Database\Factories;

use App\Models\Employe;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeFactory extends Factory
{
    protected $model = Employe::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "first_name" => $this->faker->firstName(),
            "last_name" => $this->faker->lastName(),
            "dni" => $this->faker->numerify('########'),
            "date_of_birth" => $this->faker->date('Y-m-d'),
            "address" => $this->faker->streetAddress(),
            "organization_id" => Organization::factory()
        ];
    }
}
