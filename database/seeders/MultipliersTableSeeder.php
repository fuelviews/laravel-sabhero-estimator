<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MultipliersTableSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // House style multipliers
            [
                'category' => 'house_style',
                'key'      => 'ranch',
                'value'    => 1.20,
                'image'      => 'house-styles/pbg-ranch-1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'house_style',
                'key'      => 'colonial',
                'value'    => 1.60,
                'image'      => 'house-styles/pbg-colonial-1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'house_style',
                'key'      => 'modern',
                'value'    => 1.40,
                'image'      => 'house-styles/pbg-modern-1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'house_style',
                'key'      => 'cottage',
                'value'    => 1.20,
                'image'      => 'house-styles/pbg-cottage-1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Floor multipliers
            [
                'category' => 'floor',
                'key'      => '1',
                'value'    => 1.00,
                'image'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'floor',
                'key'      => '2',
                'value'    => 1.50,
                'image'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'floor',
                'key'      => '3',
                'value'    => 1.80,
                'image'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Condition multipliers
            [
                'category' => 'condition',
                'key'      => 'excellent',
                'value'    => 1.00,
                'image'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'condition',
                'key'      => 'good',
                'value'    => 1.10,
                'image'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'condition',
                'key'      => 'fair',
                'value'    => 1.20,
                'image'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'condition',
                'key'      => 'poor',
                'value'    => 1.30,
                'image'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category'   => 'coverage',
                'key'        => 'The Entire House', // default, 100%
                'value'      => 1.00,
                'image'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category'   => 'coverage',
                'key'        => 'Three-quarters of the house', // 75%
                'value'      => 0.80,
                'image'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category'   => 'coverage',
                'key'        => 'Half the house', // 50%
                'value'      => 0.60,
                'image'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category'   => 'coverage',
                'key'        => 'A quarter of the house', // 25%
                'value'      => 0.40,
                'image'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('multipliers')->insert($data);
    }
}
