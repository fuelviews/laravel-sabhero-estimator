<?php

namespace Fuelviews\SabHeroEstimator;

use Filament\Contracts\Plugin;
use Filament\Panel;

class SabHeroEstimator implements Plugin
{
    public static function make(): static
    {
        return new static;
    }

    public function getId(): string
    {
        return 'sabhero-estimator';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            Filament\Resources\EstimatorSettingResource::class,
            Filament\Resources\HouseStyleResource::class,
            Filament\Resources\InteriorRateResource::class,
            Filament\Resources\MultiplierResource::class,
            Filament\Resources\ExteriorRateResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
