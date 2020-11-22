<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

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
            'name' => $this->faker->word,
            'description' => $this->faker->text,
            'category_parent_id' => null,
        ];
    }
}


