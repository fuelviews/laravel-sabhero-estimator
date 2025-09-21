<?php

namespace Fuelviews\SabHeroEstimator\Database\Seeders;

use Fuelviews\SabHeroEstimator\Models\Rate;
use Illuminate\Database\Seeder;

class RatesSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            // Interior rates
            ['surface_type' => 'interior_wall', 'rate' => 2.50, 'input_type' => 'measurement', 'project_type' => 'interior', 'description' => 'Interior wall painting per sq ft'],
            ['surface_type' => 'door', 'rate' => 45.00, 'input_type' => 'quantity', 'project_type' => 'interior', 'description' => 'Interior door painting per door'],
            ['surface_type' => 'window', 'rate' => 25.00, 'input_type' => 'quantity', 'project_type' => 'interior', 'description' => 'Interior window painting per window'],
            ['surface_type' => 'interior_full_base', 'rate' => 3.00, 'input_type' => 'measurement', 'project_type' => 'interior', 'description' => 'Full interior base rate per sq ft'],

            // Exterior rates
            ['surface_type' => 'exterior', 'rate' => 4.00, 'input_type' => 'measurement', 'project_type' => 'exterior', 'description' => 'Exterior painting base rate per sq ft'],
        ];

        foreach ($rates as $rate) {
            Rate::updateOrCreate(
                ['surface_type' => $rate['surface_type']],
                $rate
            );
        }
    }
}
