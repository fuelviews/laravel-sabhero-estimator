<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rates')->insert([
            [
                'surface_type' => 'Interior Wall',
                'rate'         => 2.00,
                'input_type'   => 'measurement', // e.g., walls use square footage
                'project_type' => 'interior',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'surface_type' => 'Door',
                'rate'         => 1.00,
                'input_type'   => 'quantity',    // doors are counted
                'project_type' => 'interior',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'surface_type' => 'Window',
                'rate'         => 0.75,
                'input_type'   => 'quantity',    // windows are counted
                'project_type' => 'interior',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'surface_type' => 'exterior',
                'rate'         => 3.00,
                'input_type'   => 'measurement', // e.g., for calculating based on total area
                'project_type' => 'exterior',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}