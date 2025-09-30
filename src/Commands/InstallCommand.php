<?php

namespace Fuelviews\SabHeroEstimator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class InstallCommand extends Command
{
    protected $signature = 'sab-hero-estimator:install
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
            } elseif (! $this->option('force')) {
                $this->components->warn('Use --fresh to remove old migrations or --force to overwrite them.');
            }
        }

        // Publish config (never force overwrite to preserve user settings)
        $this->components->task('Publishing configuration', function () {
            $configFile = config_path('sabhero-estimator.php');

            // Only publish if config doesn't exist, regardless of --force flag
            if (File::exists($configFile)) {
                $this->components->info('Config file already exists, skipping to preserve your settings');
                return true;
            }

            return $this->callSilent('vendor:publish', [
                '--provider' => 'Fuelviews\SabHeroEstimator\SabHeroEstimatorServiceProvider',
                '--tag' => 'sabhero-estimator-config',
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

        // Publish assets (images) to configured disk
        $this->components->task('Publishing assets to configured disk', function () {
            return $this->publishImagesToDisk();
        });

        $this->components->info('SAB Hero Estimator installed successfully!');

        // Display next steps
        $this->displayNextSteps();

        return self::SUCCESS;
    }

    protected function displayNextSteps(): void
    {
        $this->components->bulletList([
            'Add package views to your Tailwind config: <fg=yellow>\'./vendor/fuelviews/laravel-sabhero-estimator/resources/**/*.blade.php\'</fg>',
            'Add the Livewire component to your blade template: <fg=yellow>@livewire(\'estimator::project-estimator\')</fg>',
            'Register the Filament plugin to access admin resources',
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

        if (! File::isDirectory($migrationPath)) {
            return $migrations;
        }

        $files = array_merge(
            File::glob($migrationPath.'/*create_estimator_*.php'),
            File::glob($migrationPath.'/*populate_estimator_*.php')
        );

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
     * Validate that migrations exist and are properly configured
     */
    protected function validateMigrations(): void
    {
        $migrationPath = database_path('migrations');
        $structureMigrations = File::glob($migrationPath.'/*create_estimator_*.php');
        $dataMigrations = File::glob($migrationPath.'/*populate_estimator_*.php');

        if (count($structureMigrations) < 6) {
            $this->components->warn('Some structure migrations may be missing. Expected 6, found '.count($structureMigrations));
        }

        if (count($dataMigrations) < 3) {
            $this->components->info('Note: Default data migrations will be created. Found '.count($dataMigrations).' data migrations.');
        }
    }

    /**
     * Publish images directly to configured disk
     */
    protected function publishImagesToDisk(): bool
    {
        try {
            $disk = config('sabhero-estimator.media.disk');
            $targetPath = 'sabhero-estimator/images';

            // Source images from package
            $sourceDir = __DIR__ . '/../../resources/images';

            if (!File::exists($sourceDir)) {
                $this->components->error('Source images directory not found');
                return false;
            }

            $files = File::files($sourceDir);
            $count = 0;

            foreach ($files as $file) {
                $filename = $file->getFilename();
                $contents = File::get($file->getPathname());

                // Check if file exists and force option
                if (!$this->option('force') && Storage::disk($disk)->exists($targetPath . '/' . $filename)) {
                    $this->components->info('Skipping existing file: ' . $filename);
                    continue;
                }

                // Copy to configured disk
                Storage::disk($disk)->put(
                    $targetPath . '/' . $filename,
                    $contents
                );
                $count++;
            }

            $this->components->info("Published {$count} images to disk: {$disk}");

            // If using public disk, remind about storage:link
            if ($disk === 'public' && !File::exists(public_path('storage'))) {
                $this->components->warn('Remember to run: php artisan storage:link');
            }

            return true;
        } catch (\Exception $e) {
            $this->components->error('Failed to publish images: ' . $e->getMessage());
            return false;
        }
    }
}
