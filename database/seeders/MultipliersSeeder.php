<?php

namespace Fuelviews\SabHeroEstimator\Database\Seeders;

use Fuelviews\SabHeroEstimator\Models\Multiplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MultipliersSeeder extends Seeder
{
    public function run(): void
    {
        // Copy images from resources to public directory
        $this->copyImagesToPublic();

        $multipliers = [
            // House styles
            [
                'category' => 'house_style',
                'key'      => 'ranch',
                'value'    => 1.0,
                'image'    => 'pbg-ranch-1.jpg',
            ],
            [
                'category' => 'house_style',
                'key'      => 'colonial',
                'value'    => 1.15,
                'image'    => 'pbg-colonial-1.jpg',
            ],
            [
                'category' => 'house_style',
                'key'      => 'victorian',
                'value'    => 1.25,
                'image'    => 'pbg-cottage-1.jpg',
            ],
            [
                'category' => 'house_style',
                'key'      => 'modern',
                'value'    => 1.1,
                'image'    => 'pbg-modern-1.jpg',
            ],

            // Number of floors
            [
                'category' => 'floor',
                'key'      => '1',
                'value'    => 1.0,
                'image'    => null,
            ],
            [
                'category' => 'floor',
                'key'      => '2',
                'value'    => 1.2,
                'image'    => null,
            ],
            [
                'category' => 'floor',
                'key'      => '3',
                'value'    => 1.4,
                'image'    => null,
            ],

            // Paint conditions
            [
                'category' => 'condition',
                'key'      => 'excellent',
                'value'    => 1.0,
                'image'    => null,
            ],
            [
                'category' => 'condition',
                'key'      => 'good',
                'value'    => 1.1,
                'image'    => null,
            ],
            [
                'category' => 'condition',
                'key'      => 'fair',
                'value'    => 1.25,
                'image'    => null,
            ],
            [
                'category' => 'condition',
                'key'      => 'poor',
                'value'    => 1.5,
                'image'    => null,
            ],

            // Coverage options
            [
                'category' => 'coverage',
                'key'      => 'The Entire House',
                'value'    => 1.0,
                'image'    => null,
            ],
            [
                'category' => 'coverage',
                'key'      => '75% of the House',
                'value'    => 0.75,
                'image'    => null,
            ],
            [
                'category' => 'coverage',
                'key'      => '50% of the House',
                'value'    => 0.5,
                'image'    => null,
            ],
            [
                'category' => 'coverage',
                'key'      => '25% of the House',
                'value'    => 0.25,
                'image'    => null,
            ],

            // Interior extras
            [
                'category' => 'interior_extra',
                'key'      => 'trim',
                'value'    => 1.2,
                'image'    => null,
            ],
            [
                'category' => 'interior_extra',
                'key'      => 'ceilings',
                'value'    => 1.3,
                'image'    => null,
            ],
            [
                'category' => 'interior_extra',
                'key'      => 'closets',
                'value'    => 1.1,
                'image'    => null,
            ],
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

    protected function copyImagesToPublic(): void
    {
        $sourceDir = __DIR__.'/../../resources/images'; // Direct from package
        $targetDir = public_path('vendor/sabhero-estimator/images');

        // Check if source directory exists in package
        if (File::exists($sourceDir)) {
            // Create target directory if it doesn't exist
            if (! File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            // Copy all images from package to public
            File::copyDirectory($sourceDir, $targetDir);

            if ($this->command) {
                $this->command->info('Images copied from package to public directory.');
            }
        } else {
            if ($this->command) {
                $this->command->warn('Images not found in package resources directory.');
            }
        }
    }
}
