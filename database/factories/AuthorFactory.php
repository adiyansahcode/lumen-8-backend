<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Author::class;

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
            'name' => $this->faker->name,
            'description' => $this->faker->text,
        ];
    }
}

