<?php

namespace Fuelviews\SabHeroEstimator\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'sab-hero-estimator:install
                            {--seed : Seed the database with default data}
                            {--force : Force the operation to run when in production}';

    protected $description = 'Install the Sab Hero Estimator package';

    public function handle(): int
    {
        $this->components->info('Installing Sab Hero Estimator...');

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
}
