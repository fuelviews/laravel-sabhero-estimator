<?php

namespace Fuelviews\SabHeroEstimator\Contracts;

interface EstimatorCalculator
{
    /**
     * Calculate estimate based on project data
     *
     * @param  array  $data  Project data including areas, surfaces, multipliers, etc.
     * @return array Returns array with 'low' and 'high' estimate values
     */
    public function calculate(array $data): array;

    /**
     * Calculate interior project estimate
     *
     * @param  array  $data  Interior project data
     * @return float Base cost before deviation
     */
    public function calculateInterior(array $data): float;

    /**
     * Calculate exterior project estimate
     *
     * @param  array  $data  Exterior project data
     * @return float Base cost before deviation
     */
    public function calculateExterior(array $data): float;

    /**
     * Apply deviation percentage to get high/low estimates
     */
    public function applyDeviation(float $baseCost, float $deviation): array;
}
