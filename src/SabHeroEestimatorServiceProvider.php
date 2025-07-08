<?php

namespace Fuelviews\SabHeroEestimator;

use Fuelviews\SabHeroEestimator\Commands\SabHeroEestimatorCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SabHeroEestimatorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-sabhero-estimator')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_sabhero_estimator_table')
            ->hasCommand(SabHeroEestimatorCommand::class);
    }
}
