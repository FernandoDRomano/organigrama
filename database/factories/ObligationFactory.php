<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\Obligation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ObligationFactory extends Factory
{
    protected $model = Obligation::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "description" => $this->faker->sentence,
            "job_id" => Job::factory()
        ];
    }
}
