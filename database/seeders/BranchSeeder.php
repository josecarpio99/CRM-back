<?php

namespace Database\Seeders;

use App\Enums\BranchEnum;
use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = collect(BranchEnum::cases())
            ->map(fn ($item) => ['name' => $item->value])
            ->toArray();

        Branch::insert($data);
    }
}
