<?php

namespace Fuelviews\SabHeroEestimator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Fuelviews\SabHeroEestimator\SabHeroEestimator
 */
class SabHeroEestimator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Fuelviews\SabHeroEestimator\SabHeroEestimator::class;
    }
}
