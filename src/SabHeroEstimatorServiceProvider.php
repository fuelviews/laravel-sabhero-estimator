<?php

namespace Fuelviews\SabHeroEstimator;

use Fuelviews\SabHeroEstimator\Commands\SabHeroEstimatorInstallCommand;
use Fuelviews\SabHeroEstimator\Livewire\ProjectEstimator;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SabHeroEstimatorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('sabhero-estimator')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations([
                'create_estimator_rates_table',
                'create_estimator_projects_table',
                'create_estimator_areas_table',
                'create_estimator_surfaces_table',
                'create_estimator_settings_table',
                'create_estimator_multipliers_table',
                'add_interior_details_to_estimator_projects_table',
                'populate_estimator_rates_defaults',
                'populate_estimator_multipliers_defaults',
                'populate_estimator_settings_defaults',
                'add_contact_info_order_setting',
                'add_interior_scope_settings',
                'add_full_interior_assumption_label_setting',
            ])
            ->hasCommands([
                SabHeroEstimatorInstallCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        // Register singleton services
        $this->app->singleton('estimator', function () {
            return new SabHeroEstimator;
        });

        // Register contracts
        $this->app->bind(
            \Fuelviews\SabHeroEstimator\Contracts\EstimatorCalculator::class,
            \Fuelviews\SabHeroEstimator\Services\CalculationService::class
        );
    }

    public function packageBooted(): void
    {
        // Register Livewire components
        if (class_exists(Livewire::class)) {
            Livewire::component('estimator::project-estimator', ProjectEstimator::class);
        }
    }
}
