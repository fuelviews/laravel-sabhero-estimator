<?php

namespace Fuelviews\SabHeroEstimator;

use Fuelviews\SabHeroEstimator\Commands\InstallCommand;
use Fuelviews\SabHeroEstimator\Commands\SeedCommand;
use Fuelviews\SabHeroEstimator\Http\Livewire\ProjectEstimator;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Commands\InstallCommand as SpatieInstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SabHeroEstimatorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-sabhero-estimator')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations([
                'create_estimator_rates_table',
                'create_estimator_projects_table',
                'create_estimator_areas_table',
                'create_estimator_surfaces_table',
                'create_estimator_settings_table',
                'create_estimator_multipliers_table',
            ])
            ->hasCommands([
                InstallCommand::class,
                SeedCommand::class,
            ])
            ->hasRoute('web')
            ->hasInstallCommand(function (SpatieInstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->copyAndRegisterServiceProviderInApp();
            });
    }

    public function packageRegistered(): void
    {
        // Register singleton services
        $this->app->singleton('estimator', function () {
            return new EstimatorManager;
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

        // Filament plugin is registered directly in the AdminPanelProvider

        // Load additional routes if needed
        if (config('sabhero-estimator.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        // Publish seeders
        $this->publishes([
            __DIR__.'/../database/seeders' => database_path('seeders'),
        ], 'sabhero-estimator-seeders');
    }
}
