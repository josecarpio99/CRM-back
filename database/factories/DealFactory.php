<?php

namespace Database\Factories;

use App\Enums\DealStatusEnum;
use App\Enums\DealTypeEnum;
use App\Models\User;
use App\Models\Source;
use App\Models\Category;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deal>
 */
class DealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $users = User::pluck('id')->toArray();
        $customers = Customer::pluck('id')->toArray();
        $sources = Source::pluck('id')->toArray();
        $categories = Category::pluck('id')->toArray();
        // $statuses = DealStatusEnum::cases();

        return [
            'type' => fake()->randomElement(DealTypeEnum::cases()),
            'customer_id' => fake()->randomElement($customers),
            'owner_id' => fake()->randomElement($users),
            'source_id' => fake()->randomElement($sources),
            'category_id' => fake()->randomElement($categories),
            'name' => fake()->text(20),
            'value' => fake()->numberBetween(10000, 10000000),
            'win_probability' => fake()->numberBetween(1, 100),
            // 'deal_pipeline_id' => 1,
            // 'deal_pipeline_stage_id' => 1,
            'estimated_size' => fake()->numberBetween(10000, 50000000),
            'estimated_close_date' => fake()->dateTimeBetween('-1 week', '+8 weeks'),
            'customer_responsiveness' => fake()->randomElement(['Muy responsivo', 'Poco responsivo', 'Normal']),
            // 'status' => fake()->randomElement($statuses),
        ];
    }
}
