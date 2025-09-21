<?php

namespace Fuelviews\SabHeroEstimator\Database\Seeders;

use Fuelviews\SabHeroEstimator\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'deviation_percentage' => '35',
            'paint_condition_label' => 'Condition of Existing Paint',
            'paint_condition_default_option' => 'Select condition',
            'coverage_label' => 'How much of the house is being painted?',
            'select_house_style_label' => 'Select House Style:',
            'number_of_floors_label' => 'Number of Floors',
            'number_of_floors_default_option' => 'Select the number of floors',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
