<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Book;
use App\Models\BookImg;
use App\Models\Author;
use App\Models\Category;
use App\Models\Language;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Book::class;

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
            'isbn' => $this->faker->isbn13,
            'title' => $this->faker->word,
            'publication_date' => $this->faker->date('Y-m-d', 'now'),
            'weight' => $this->faker->randomDigitNotNull,
            'wide' => $this->faker->randomDigitNotNull,
            'long' => $this->faker->randomDigitNotNull,
            'page' => $this->faker->randomDigitNotNull,
            'page' => $this->faker->randomDigitNotNull,
            'description' => $this->faker->text,
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Book $book) {
            $language = Language::all()->random();
            $book->language()->associate($language);

            $publisher = Publisher::all()->random();
            $book->publisher()->associate($publisher);

            $category = Category::all()->random();
            $book->category()->associate($category);

            $author = Author::all();
            $book->author()->attach(
                $author->random(rand(1, 3))->pluck('id')->toArray()
            );

            $book->save();
        });
    }
}
