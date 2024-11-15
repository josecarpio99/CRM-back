<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Sector;
use App\Models\Source;
use App\Models\Category;
use App\Models\CustomerStatus;
use App\Models\PotentialCustomerStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $users = User::pluck('id')->toArray();
        $categories = Category::pluck('id')->toArray();
        $sources = Source::pluck('id')->toArray();

        return [
            'is_company' => fake()->boolean(),
            'name' => fake()->name(),
            'mobile' => fake()->phoneNumber(),
            'email' => fake()->email(),
            'origin' => fake()->text(50),
            'city' => fake()->city(),
            'state' => null,
            'postcode' => fake()->postcode(),
            'address' => fake()->address(),
            'website' => fake()->url(),
            'phone' => fake()->phoneNumber(),
            'position' => fake()->jobTitle(),
            'facebook' => null,
            'twitter' => null,
            'linkedin' => null,
            'skype' => null,
            'description' => fake()->text(100),
            'owner_id' => fake()->randomElement($users),
            // 'country_id' => 142,
            'category_id' => fake()->randomElement($categories),
            'parent_id' => null,
            'source_id' => fake()->randomElement($sources),
        ];
    }
}
