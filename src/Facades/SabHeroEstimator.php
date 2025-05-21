<?php

namespace Fuelviews\SabHeroEstimator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Fuelviews\SabHeroEstimator\SabHeroEstimator
 */
class SabHeroEstimator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Fuelviews\SabHeroEstimator\SabHeroEstimator::class;
    }
}
