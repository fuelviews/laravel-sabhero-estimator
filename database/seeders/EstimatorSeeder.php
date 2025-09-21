<?php

namespace Fuelviews\SabHeroEstimator\Database\Seeders;

use Illuminate\Database\Seeder;

class EstimatorSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            RatesSeeder::class,
            MultipliersSeeder::class,
        ]);
    }
}
