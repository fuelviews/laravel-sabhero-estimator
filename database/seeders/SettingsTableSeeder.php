<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // This will create or update the record with key 'deviation_percentage'
        // and set its value to '15' (representing 15%).
        Setting::updateOrCreate(
            ['key' => 'deviation_percentage'],
            ['value' => '15']
        );
        // Add the coverage label setting.
        Setting::updateOrCreate(
            ['key' => 'coverage_label'],
            ['value' => 'How much of the house is being painted?']
        );
        Setting::updateOrCreate(
            ['key' => 'number_of_floors_label'],
            ['value' => 'Number of Floors']
        );
        Setting::updateOrCreate(
            ['key' => 'number_of_floors_default_option'],
            ['value' => 'Select the number of floors']
        );
        Setting::updateOrCreate(
            ['key' => 'select_house_style_label'],
            ['value' => 'Select House Style:']
        );
        Setting::updateOrCreate(
            ['key' => 'paint_condition_label'],
            ['value' => 'Condition of Existing Paint']
        );
        Setting::updateOrCreate(
            ['key' => 'paint_condition_default_option'],
            ['value' => 'Select condition']
        );
    }
}