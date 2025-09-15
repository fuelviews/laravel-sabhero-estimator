<?php

namespace Fuelviews\SabHeroEstimator\Database\Seeders;

use Fuelviews\SabHeroEstimator\Models\Multiplier;
use Illuminate\Database\Seeder;

class MultipliersSeeder extends Seeder
{
    public function run(): void
    {
        $multipliers = [
            // House styles
            ['category' => 'house_style', 'key' => 'ranch', 'value' => 1.0, 'image' => null],
            ['category' => 'house_style', 'key' => 'colonial', 'value' => 1.15, 'image' => null],
            ['category' => 'house_style', 'key' => 'victorian', 'value' => 1.25, 'image' => null],
            ['category' => 'house_style', 'key' => 'modern', 'value' => 1.1, 'image' => null],

            // Number of floors
            ['category' => 'floor', 'key' => '1', 'value' => 1.0, 'image' => null],
            ['category' => 'floor', 'key' => '2', 'value' => 1.2, 'image' => null],
            ['category' => 'floor', 'key' => '3', 'value' => 1.4, 'image' => null],

            // Paint conditions
            ['category' => 'condition', 'key' => 'excellent', 'value' => 1.0, 'image' => null],
            ['category' => 'condition', 'key' => 'good', 'value' => 1.1, 'image' => null],
            ['category' => 'condition', 'key' => 'fair', 'value' => 1.25, 'image' => null],
            ['category' => 'condition', 'key' => 'poor', 'value' => 1.5, 'image' => null],

            // Coverage options
            ['category' => 'coverage', 'key' => 'The Entire House', 'value' => 1.0, 'image' => null],
            ['category' => 'coverage', 'key' => '75% of the House', 'value' => 0.75, 'image' => null],
            ['category' => 'coverage', 'key' => '50% of the House', 'value' => 0.5, 'image' => null],
            ['category' => 'coverage', 'key' => '25% of the House', 'value' => 0.25, 'image' => null],

            // Interior extras
            ['category' => 'interior_extra', 'key' => 'trim', 'value' => 1.2, 'image' => null],
            ['category' => 'interior_extra', 'key' => 'ceilings', 'value' => 1.3, 'image' => null],
            ['category' => 'interior_extra', 'key' => 'closets', 'value' => 1.1, 'image' => null],
        ];

        foreach ($multipliers as $multiplier) {
            Multiplier::updateOrCreate(
                [
                    'category' => $multiplier['category'],
                    'key' => $multiplier['key'],
                ],
                $multiplier
            );
        }
    }
}
