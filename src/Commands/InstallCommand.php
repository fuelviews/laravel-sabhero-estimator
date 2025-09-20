<?php

namespace Fuelviews\SabHeroEstimator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'sab-hero-estimator:install
                            {--seed : Seed the database with default data}
                            {--force : Force overwrite of existing files (config, migrations, etc.)}
                            {--fresh : Remove old estimator migrations before publishing new ones}';

    protected $description = 'Install the Sab Hero Estimator package and optionally clean up old migrations';

    public function handle(): int
    {
        $this->components->info('Installing Sab Hero Estimator...');

        // Check for existing estimator migrations
        $existingMigrations = $this->findExistingEstimatorMigrations();

        if (count($existingMigrations) > 0) {
            $this->components->warn('Found '.count($existingMigrations).' existing estimator migration(s).');

            if ($this->option('fresh')) {
                $this->components->task('Removing old estimator migrations', function () use ($existingMigrations) {
                    return $this->cleanOldMigrations($existingMigrations);
                });
            } elseif (!$this->option('force')) {
                $this->components->warn('Use --fresh to remove old migrations or --force to overwrite them.');
            }
        }

        // Publish config
        $this->components->task('Publishing configuration', function () {
            return $this->callSilent('vendor:publish', [
                '--provider' => 'Fuelviews\SabHeroEstimator\SabHeroEstimatorServiceProvider',
                '--tag' => 'sabhero-estimator-config',
                '--force' => $this->option('force'),
            ]) === 0;
        });

        // Publish migrations
        $this->components->task('Publishing migrations', function () {
            return $this->callSilent('vendor:publish', [
                '--provider' => 'Fuelviews\SabHeroEstimator\SabHeroEstimatorServiceProvider',
                '--tag' => 'sabhero-estimator-migrations',
                '--force' => $this->option('force'),
            ]) === 0;
        });

        // Validate migrations use correct config path
        $this->validateMigrations();

        // Run migrations
        $runMigrations = $this->components->confirm('Would you like to run the migrations?', true);
        if ($runMigrations) {
            $this->components->task('Running migrations', function () {
                return $this->callSilent('migrate') === 0;
            });
        }

        // Seed default data
        if ($this->option('seed') || $this->components->confirm('Would you like to seed the database with default estimator data?', true)) {
            $this->components->task('Seeding default data', function () {
                return $this->call('sab-hero-estimator:seed') === 0;
            });
        }

        // Publish views (optional)
        $publishViews = $this->components->confirm('Would you like to publish the views for customization?', false);
        if ($publishViews) {
            $this->components->task('Publishing views', function () {
                return $this->callSilent('vendor:publish', [
                    '--provider' => 'Fuelviews\SabHeroEstimator\SabHeroEstimatorServiceProvider',
                    '--tag' => 'sabhero-estimator-views',
                    '--force' => $this->option('force'),
                ]) === 0;
            });
        }

        $this->components->info('Sab Hero Estimator installed successfully!');

        // Display next steps
        $this->displayNextSteps();

        return self::SUCCESS;
    }

    protected function displayNextSteps(): void
    {
        $this->components->bulletList([
            'Add the Livewire component to your blade template: <fg=yellow>@livewire(\'estimator::project-estimator\')</fg>',
            'Access the admin panel via Filament to configure rates and multipliers',
            'Review the published config file: <fg=yellow>config/sabhero-estimator.php</fg>',
        ]);
    }

    /**
     * Find existing estimator migrations in the application
     */
    protected function findExistingEstimatorMigrations(): array
    {
        $migrationPath = database_path('migrations');
        $migrations = [];

        if (!File::isDirectory($migrationPath)) {
            return $migrations;
        }

        $files = File::glob($migrationPath.'/*create_estimator_*.php');

        foreach ($files as $file) {
            $migrations[] = $file;
        }

        return $migrations;
    }

    /**
     * Clean old estimator migrations
     */
    protected function cleanOldMigrations(array $migrations): bool
    {
        try {
            foreach ($migrations as $migration) {
                File::delete($migration);
                $this->components->info('Removed: '.basename($migration));
            }
            return true;
        } catch (\Exception $e) {
            $this->components->error('Failed to remove migrations: '.$e->getMessage());
            return false;
        }
    }

    /**
     * Validate that migrations use the correct config path
     */
    protected function validateMigrations(): void
    {
        $migrationPath = database_path('migrations');
        $files = File::glob($migrationPath.'/*create_estimator_*.php');
        $hasOldPath = false;

        foreach ($files as $file) {
            $content = File::get($file);

            // Check for old config path
            if (str_contains($content, 'sabhero-estimator.database.table_prefix')) {
                $hasOldPath = true;
                $this->components->warn('Migration uses old config path: '.basename($file));
            }
        }

        if ($hasOldPath) {
            $this->components->warn('⚠️  Some migrations use the old config path (database.table_prefix).');
            $this->components->warn('⚠️  The current config uses the new path (table.prefix).');
            $this->components->info('Run with --fresh --force to clean up and republish migrations.');
        }
    }
}
