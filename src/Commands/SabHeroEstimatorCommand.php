<?php

namespace Fuelviews\SabHeroEstimator\Commands;

use Illuminate\Console\Command;

class SabHeroEstimatorCommand extends Command
{
    public $signature = 'laravel-sabhero-estimator';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
