<?php

declare(strict_types=1);

use App\Models\Book;
use App\Models\BookImg;
use App\Models\Author;
use App\Models\Category;
use App\Models\Language;
use App\Models\Publisher;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        // Create language
        $languageDb = new Language();
        $languageDb->created_at = $faker->dateTimeBetween('-20 years', 'now');
        $languageDb->uuid = $faker->uuid;
        $languageDb->name = 'English';
        $languageDb->description = 'english';
        $languageDb->icon = 'us';
        $languageDb->save();

        $languageDb = new Language();
        $languageDb->created_at = $faker->dateTimeBetween('-20 years', 'now');
        $languageDb->uuid = $faker->uuid;
        $languageDb->name = 'Indonesia';
        $languageDb->description = 'indonesia';
        $languageDb->icon = 'id';
        $languageDb->save();

        // Create random author
        Author::factory()->count(100)->create();

        // Create random category
        Category::factory()->count(100)->create();

        // Create random publisher
        Publisher::factory()->count(100)->create();

        // Create random book
        Book::factory()->count(100)->create();
    }
}
