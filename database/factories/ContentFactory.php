<?php

namespace Database\Factories;

use App\Models\Content;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Content>
 */
class ContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(3, true);

        return [
            'title' => $title,
            'thumbnail' => '/images/'.fake()->uuid().'.jpg',
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(['Action', 'Comedy', 'Drama', 'Documentary']),
            'slug' => str($title)->slug().'-'.fake()->unique()->numberBetween(1, 1000000),
        ];
    }
}
