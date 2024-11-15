<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\PotentialCustomerStatus;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BranchSeeder::class,
            UserSeeder::class,
            // CountrySeeder::class,
            // SectorSeeder::class,
            // CustomerStatusSeeder::class,
            // PotentialCustomerStatusSeeder::class,
            CategorySeeder::class,
            SourceSeeder::class,
            CustomerSeeder::class,
            LeadSeeder::class,
            // DealPipelineSeeder::class,
            // DealPipelineStageSeeder::class,
            DealSeeder::class,
            // SmartListSeeder::class,
        ]);
    }
}
