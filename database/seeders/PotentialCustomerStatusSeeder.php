<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PotentialCustomerStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PotentialCustomerStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'cliente potencial actual'
            ],
            [
                'name' => 'cliente potencial perdido'
            ],
            [
                'name' => 'no es cliente potencial'
            ]
        ];

        PotentialCustomerStatus::insert($data);
    }
}
