<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Sector;
use App\Models\Source;
use App\Models\Category;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $users = User::pluck('id')->toArray();
        // $sectors = Sector::pluck('id')->toArray();
        $sources = Source::pluck('id')->toArray();
        $categories = Category::pluck('id')->toArray();

        $name = fake()->name();

        return [
            'name' => $name,
            'company_name' => $name,
            'mobile' => fake()->phoneNumber(),
            'phone' => fake()->phoneNumber(),
            'position' => fake()->jobTitle(),
            'email' => fake()->email(),
            'city' => fake()->city(),
            'state' => null,
            'postcode' => fake()->postcode(),
            'address' => fake()->address(),
            'owner_id' => fake()->randomElement($users),
            // 'sector_id' => fake()->randomElement($sectors),
            'source_id' => fake()->randomElement($sources),
            'category_id' => fake()->randomElement($categories),
            // 'country_id' => 142,
            'requirement' => fake()->text(50)
        ];
    }
}
