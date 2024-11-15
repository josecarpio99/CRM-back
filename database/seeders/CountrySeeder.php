<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Country::count() == 0) {
            $path = base_path('database/seeders/countries.sql');
            $sql = file_get_contents($path);
            DB::unprepared($sql);
        }
    }
}
