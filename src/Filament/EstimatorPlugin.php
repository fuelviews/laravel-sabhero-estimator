<?php

namespace Fuelviews\SabHeroEstimator\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Fuelviews\SabHeroEstimator\Filament\Resources\MultiplierResource;
use Fuelviews\SabHeroEstimator\Filament\Resources\ProjectResource;
use Fuelviews\SabHeroEstimator\Filament\Resources\RateResource;
use Fuelviews\SabHeroEstimator\Filament\Resources\SettingResource;

class EstimatorPlugin implements Plugin
{
    public static function make(): static
    {
        return new static;
    }

    public function getId(): string
    {
        return 'sab-hero-estimator';
    }

    public function register(Panel $panel): void
    {
        $panel->resources($this->getResources());
    }

    public function boot(Panel $panel): void
    {
        // Plugin boot logic if needed
    }

    protected function getResources(): array
    {
        return [
            SettingResource::class,
            MultiplierResource::class,
            RateResource::class,
            ProjectResource::class,
        ];
    }
}
