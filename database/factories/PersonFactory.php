<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Country;
use App\Models\Person;
use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Person::class;

    public function definition(): array
    {

        $city =  City::inRandomOrder()->first();
        return [
            'name' => fake()->name(),
            'family' => fake()->name(),
            'father_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'city_id' => $city?->id,
            'province_id' => $city?->province_id,
            'country_id' => $city?->province?->country_id ,
            'gender' =>rand(1,2),
            'addr' => $this->faker->address(),
            // 'photo' => $this->faker->image(dir:storage_path('app/public'),width:400,height: 300,format:'jpg',fullPath:false) 
        ];
    }
}
