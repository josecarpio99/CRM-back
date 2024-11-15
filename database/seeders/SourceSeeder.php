<?php

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Prospección'
            ],
            [
                'name' => 'Publicidad - Guardia'
            ],
            [
                'name' => 'Recompra'
            ]
        ];

        Source::insert($data);
    }
}
