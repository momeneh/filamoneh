<?php

namespace Database\Factories;

use App\Models\Paper;
use App\Models\User;
use App\Models\Country;
use App\Models\PaperType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Paper>
 */
class PaperFactory extends Factory
{
    protected $model = Paper::class;

    public function definition(): array
    {
        $user = User::factory()->create();
        $country = Country::inRandomOrder()->first() ?? Country::factory()->create();
        $paperType = PaperType::inRandomOrder()->first() ?? PaperType::factory()->create();

        return [
            'title' => $this->faker->sentence(6),
            'paper_type_id' => $paperType->id,
            'country_id' => $country->id,
            'title_url' => $this->faker->slug(),
            'priority' => $this->faker->numberBetween(1, 10),
            'paper_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'doi' => '10.1000/' . $this->faker->unique()->regexify('[A-Za-z0-9]{6}'),
            'count_page' => $this->faker->numberBetween(5, 50),
            'refrence_link' => $this->faker->url(),
            'is_accepted' => $this->faker->boolean(80),
            'is_visible' => $this->faker->boolean(90),
            'is_archived' => $this->faker->boolean(10),
            'abstract' => $this->faker->paragraph(3),
            'description' => $this->faker->paragraphs(2, true),
            'insert_user_id' => $user->id,
            'edit_user_id' => $user->id,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_accepted' => true,
        ]);
    }

    public function visible(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => true,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
        ]);
    }
}
