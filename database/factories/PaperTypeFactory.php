<?php

namespace Database\Factories;

use App\Models\PaperType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaperType>
 */
class PaperTypeFactory extends Factory
{
    protected $model = PaperType::class;

    public function definition(): array
    {
        $types = [
            'Research Paper',
            'Review Paper',
            'Conference Paper',
            'Journal Article',
            'Technical Report',
            'Thesis',
            'Dissertation',
            'Book Chapter',
            'Short Paper',
            'Poster'
        ];

        return [
            'title' => $this->faker->randomElement($types),
        ];
    }
}
