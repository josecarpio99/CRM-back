<?php

namespace Database\Seeders;

use App\Models\DealPipelineStage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DealPipelineStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'deal_pipeline_id' => 1,
                'code' => 'Oportunidad',
                'name' => 'Oportunidad',
                'probability' => 5,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'deal_pipeline_id' => 1,
                'code' => 'Cotizado',
                'name' => 'Cotizado',
                'probability' => 20,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'deal_pipeline_id' => 1,
                'code' => 'Ganado y por entregar',
                'name' => 'Ganado y por entregar',
                'probability' => 100,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'deal_pipeline_id' => 1,
                'code' => 'Ganado',
                'name' => 'Ganado',
                'probability' => null,
                'is_active' => false,
                'sort_order' => 4,
            ],
            [
                'deal_pipeline_id' => 1,
                'code' => 'Perdido',
                'name' => 'Perdido',
                'probability' => null,
                'is_active' => false,
                'sort_order' => 4,
            ],
            [
                'deal_pipeline_id' => 1,
                'code' => 'No cualificado',
                'name' => 'No cualificado',
                'probability' => null,
                'is_active' => false,
                'sort_order' => 4,
            ],
        ];

        DealPipelineStage::insert($data);
    }
}
