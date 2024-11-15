<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // [
            //     'name' => 'A - Compras de 2MDP anual',
            //     'group' => 'customer'
            // ],
            // [
            //     'name' => 'B - Compra de 250k a 2MDP anual',
            //     'group' => 'customer'
            // ],
            // [
            //     'name' => 'A (Potencial de Compra Alto)',
            //     'group' => 'lead'
            // ],
            // [
            //     'name' => 'B (Potencial de Compra Regular)',
            //     'group' => 'lead'
            // ],
            [
                'name' => 'AAA - Corporativos e Industrias',
                'group' => 'deal'
            ],
            [
                'name' => 'AA - Pymes',
                'group' => 'deal'
            ],
            [
                'name' => 'A - Micro y Personas Físicas',
                'group' => 'deal'
            ]
        ];

        Category::insert($data);
    }
}
