<?php

namespace Database\Seeders;

use App\Models\CustomerStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'cliente actual'
            ],
            [
                'name' => 'cliente anterior'
            ],
            [
                'name' => 'no es cliente'
            ]
        ];

        CustomerStatus::insert($data);
    }
}
