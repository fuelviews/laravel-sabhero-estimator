<?php

namespace Fuelviews\SabHeroEstimator;

use Fuelviews\SabHeroEstimator\Livewire\ProjectEstimator;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SabHeroEstimatorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('sabhero-estimator')
            ->hasConfigFile('sabhero-estimator')
            ->hasViews('sabhero-estimator')
            ->hasMigrations([
                'create_sabhero_estimator_areas_table',
                'create_sabhero_estimator_multipliers_table',
                'create_sabhero_estimator_projects_table',
                'create_sabhero_estimator_rates_table',
                'create_sabhero_estimator_settings_table',
                'create_sabhero_estimator_surfaces_table'
            ]);
    }

    public function bootingPackage(): void
    {
        Livewire::component('sabhero-portfolio::project-estimator', ProjectEstimator::class);
    }
}
