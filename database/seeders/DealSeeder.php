<?php

namespace Database\Seeders;

use App\Models\Deal;
use App\Models\User;
use App\Models\Source;
use App\Models\Category;
use App\Models\Customer;
use App\Enums\DealStatusEnum;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use App\Models\DealPipelineStage;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;
use App\Enums\DealEstimatedCloseDateRangeEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // en proceso deals
        Deal::factory(20)->create();

        // won deals
        Deal::factory(5)->create([
            'status' => DealStatusEnum::Won->value,
            'stage_moved_at' => Carbon::now(),
        ]);

    }
}
