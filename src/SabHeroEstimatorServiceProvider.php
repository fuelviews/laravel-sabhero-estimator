<?php

namespace Fuelviews\SabHeroEstimator;

use Fuelviews\SabHeroEstimator\Livewire\ProjectEstimator;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Filament\SpatieLaravelMediaLibraryPlugin;

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
                'create_estimator_rates_table',
                'create_estimator_projects_table',
                'create_estimator_areas_table',
                'create_estimator_surfaces_table',
                'create_estimator_settings_table',
                'create_estimator_multipliers_table',
                'seed_estimator_settings_table',
                'seed_estimator_multipliers_table',
                'seed_estimator_rates_table'
            ]);
    }

    public function bootingPackage(): void
    {
        Livewire::component('sabhero-estimator::project-estimator', ProjectEstimator::class);
    }
}
