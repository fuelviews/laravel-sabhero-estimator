<?php

namespace Fuelviews\SabHeroEstimator;

use Fuelviews\SabHeroEstimator\Commands\InstallCommand;
use Fuelviews\SabHeroEstimator\Livewire\ProjectEstimator;
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
                'populate_estimator_rates_defaults',
                'populate_estimator_multipliers_defaults',
                'populate_estimator_settings_defaults',
                'fix_estimator_multiplier_image_paths',
            ])
            ->hasCommands([
                InstallCommand::class,
            ])
            ->hasInstallCommand(function (SpatieInstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->publishAssets()
                    ->askToRunMigrations()
                    ->copyAndRegisterServiceProviderInApp();
            });
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

        // Filament plugin is registered directly in the AdminPanelProvider

        // Routes not needed - estimator is used as embedded Livewire component

        // Publish images to configured disk
        $this->publishImagesToDisk();
    }

    protected function publishImagesToDisk(): void
    {
        // Publish command to copy images to configured disk
        $this->publishes([
            __DIR__.'/../resources/images' => $this->getImagePublishPath(),
        ], 'sabhero-estimator-assets');

        // Also register a command to copy images during install
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Images are copied via the vendor:publish command
            ]);
        }
    }

    protected function getImagePublishPath(): string
    {
        $disk = config('sabhero-estimator.media.disk', 'public');
        $path = 'estimator/images';

        // If using public disk, return the public path
        if ($disk === 'public') {
            return public_path($path);
        }

        // For other disks, use the disk's root path
        try {
            $diskConfig = config("filesystems.disks.{$disk}");
            if ($diskConfig && isset($diskConfig['root'])) {
                return rtrim($diskConfig['root'], '/') . '/' . ltrim($path, '/');
            }
        } catch (\Exception $e) {
            // Fall back to public path if disk config is not found
        }

        // Default fallback to public path
        return public_path($path);
    }
}
