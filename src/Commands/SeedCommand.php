<?php

namespace Fuelviews\SabHeroEstimator\Commands;

use Illuminate\Console\Command;

class SeedCommand extends Command
{
    protected $signature = 'sab-hero-estimator:seed
                            {--force : Force the operation to run when in production}';

    protected $description = 'Seed the database with default estimator data';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->components->error('Command not allowed in production. Use --force to override.');

            return self::FAILURE;
        }

        $this->components->info('Seeding Sab Hero Estimator data...');

        $this->components->task('Seeding estimator data', function () {
            $seedersPath = __DIR__ . '/../../database/seeders/';

            // Require all seeder files
            require_once $seedersPath . 'SettingsSeeder.php';
            require_once $seedersPath . 'RatesSeeder.php';
            require_once $seedersPath . 'MultipliersSeeder.php';
            require_once $seedersPath . 'EstimatorSeeder.php';

            $seeder = new \Fuelviews\SabHeroEstimator\Database\Seeders\EstimatorSeeder;
            $seeder->run();

            return true;
        });

        $this->components->info('Default data seeded successfully!');

        return self::SUCCESS;
    }
}

