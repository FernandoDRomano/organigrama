<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Job;
use App\Models\JobLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    protected $model = Job::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name" => $this->faker->jobTitle(),
            "job_level_id" => JobLevel::factory(),
            "department_id" => Department::factory()
        ];
    }
}
