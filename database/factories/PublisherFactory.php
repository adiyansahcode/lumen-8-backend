<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublisherFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Publisher::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'created_at' => $this->faker->dateTimeBetween('-20 years', 'now'),
            'uuid' => $this->faker->uuid,
            'name' => $this->faker->company,
            'description' => $this->faker->text,
            'city' => $this->faker->city,
        ];
    }
}

