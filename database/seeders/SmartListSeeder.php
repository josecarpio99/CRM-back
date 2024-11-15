<?php

namespace Database\Seeders;

use App\Models\SmartList;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SmartListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SmartList::create([
        //     'user_id' => 1,
        //     'resource_type' => 'customer',
        //     'name' => 'Prospección hoy CdMx',
        //     'definition' => [
        //         'query' => [
        //             'page' => 1,
        //             'limit' => 'all',
        //             'search' => '',
        //             'sort' => '',
        //             'filters' => [
        //                 'name' => [
        //                     'value' => '',
        //                     'comparison' => '',
        //                 ],
        //                 'owner' => [
        //                     'value' => '1',
        //                     'comparison' => '',
        //                 ],
        //                 'status' => [
        //                     'value' => '',
        //                     'comparison' => '',
        //                 ],
        //                 'category_id' => [
        //                     'value' => '',
        //                     'comparison' => ''
        //                 ],
        //                 'created_at' => [
        //                    'value' => 'today',
        //                     'comparison' => ''
        //                 ],
        //             ]
        //         ]
        //     ]
        // ]);

        // for ($i=0; $i < 18; $i++) {
        //     SmartList::create([
        //     'user_id' => 1,
        //     'resource_type' => 'customer',
        //     'name' => 'Lista de test #' . $i+1,
        //     'definition' => [
        //         'query' => [
        //             'page' => 1,
        //             'limit' => 'all',
        //             'search' => '',
        //             'sort' => '',
        //             'filters' => [
        //                 'name' => [
        //                     'value' => '',
        //                     'comparison' => '',
        //                 ],
        //                 'owner' => [
        //                     'value' => '1',
        //                     'comparison' => '',
        //                 ],
        //                 'status' => [
        //                     'value' => '',
        //                     'comparison' => '',
        //                 ],
        //                 'category_id' => [
        //                     'value' => '',
        //                     'comparison' => ''
        //                 ],
        //                 'created_at' => [
        //                    'value' => '',
        //                     'comparison' => ''
        //                 ],
        //             ]
        //         ]
        //     ]
        // ]);
        // }

        // SmartList::create([
        //     'user_id' => 1,
        //     'resource_type' => 'oportunidad',
        //     'name' => 'Test oportunidad valor',
        //     'definition' => [
        //         'query' => [
        //             'page' => 1,
        //             'limit' => 'all',
        //             'search' => '',
        //             'sort' => '',
        //             'filters' => [
        //                 'type' => [
        //                     'value' => 'oportunidad',
        //                     'comparison' => '='
        //                 ],
        //                 'name' => [
        //                     'value' => '',
        //                     'comparison' => '='
        //                 ],
        //                 'source' => [
        //                     'value' => '',
        //                     'comparison' => '='
        //                 ],
        //                 'owner' => [
        //                     'value' => '',
        //                     'comparison' => '='
        //                 ],
        //                 'value' => [
        //                     'value' => '0,100000',
        //                     'comparison' => '='
        //                 ],
        //                 'created_at' => [
        //                     'value' => 'last_quarter',
        //                     'comparison' => '='
        //                 ]
        //             ]
        //         ]
        //     ]
        // ]);
        SmartList::create([
            'user_id' => 1,
            'resource_type' => 'cotizado',
            'name' => 'Test cotizado valor',
            'definition' => [
                'query' => [
                    'page' => 1,
                    'limit' => 'all',
                    'search' => '',
                    'sort' => '',
                    'filters' => [
                        'type' => [
                            'value' => 'cotizado',
                            'comparison' => '='
                        ],
                        'name' => [
                            'value' => '',
                            'comparison' => '='
                        ],
                        'source' => [
                            'value' => '',
                            'comparison' => '='
                        ],
                        'value' => [
                            'value' => '0,100000',
                            'comparison' => '='
                        ],
                        'owner' => [
                            'value' => '',
                            'comparison' => '='
                        ],
                        'created_at' => [
                            'value' => '',
                            'comparison' => '='
                        ]
                    ]
                ]
            ]
        ]);

        // SmartList::create([
        //     'user_id' => 1,
        //     'resource_type' => 'lead',
        //     'name' => 'Año pasado Lead CdMx',
        //     'definition' => [
        //         'query' => [
        //             'page' => 1,
        //             'limit' => 'all',
        //             'search' => '',
        //             'sort' => '',
        //             'filters' => [
        //                 'name' => [
        //                     'value' => '',
        //                     'comparison' => '',
        //                 ],
        //                 'owner' => [
        //                     'value' => '',
        //                     'comparison' => '',
        //                 ],
        //                 'status' => [
        //                     'value' => 'nuevo,asignado',
        //                     'comparison' => '',
        //                 ],
        //                 'source' => [
        //                     'value' => '',
        //                     'comparison' => ''
        //                 ],
        //                 'created_at' => [
        //                     'value' => 'last_year',
        //                     'comparison' => ''
        //                 ],
        //             ]
        //         ]
        //     ]
        // ]);
    }
}
