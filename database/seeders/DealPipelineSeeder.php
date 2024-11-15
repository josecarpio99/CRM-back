<?php

namespace Database\Seeders;

use App\Models\DealPipeline;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DealPipelineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DealPipeline::create([
            'name' => 'Pipeline de ventas por defecto',
            'is_default' => false
        ]);
    }
}
