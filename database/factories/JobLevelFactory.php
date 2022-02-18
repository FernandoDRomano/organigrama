<?php

namespace Database\Factories;

use App\Models\JobLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobLevelFactory extends Factory
{
    protected $model = JobLevel::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name" => $this->faker->numerify('Level - #'),
            "hierarchy" => $this->faker->numberBetween(1, 10)
        ];
    }
}
