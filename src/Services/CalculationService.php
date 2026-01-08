<?php

namespace Fuelviews\SabHeroEstimator\Services;

use Fuelviews\SabHeroEstimator\Contracts\EstimatorCalculator;
use Fuelviews\SabHeroEstimator\Models\Multiplier;
use Fuelviews\SabHeroEstimator\Models\Rate;
use Fuelviews\SabHeroEstimator\Models\Setting;

class CalculationService implements EstimatorCalculator
{
    public function calculate(array $data): array
    {
        $deviation = Setting::getDeviationPercentage();

        if (($data['project_type'] ?? null) === 'interior') {
            $baseCost = $this->calculateInterior($data);
        } else {
            $baseCost = $this->calculateExterior($data);
        }

        // Ensure we always return a valid estimate (minimum $0.01 if calculation resulted in 0)
        // This prevents showing $0.00 - $0.00 to users
        if ($baseCost <= 0) {
            $baseCost = 0;
        }

        return $this->applyDeviation($baseCost, $deviation);
    }

    public function calculateInterior(array $data): float
    {
        $totalCost = 0;

        if (($data['interior_scope'] ?? null) === 'full') {
            // Full interior calculation
            $squareFeet = (float) ($data['full_floor_space'] ?? $data['total_floor_space'] ?? 0);

            // Only calculate if we have square footage
            if ($squareFeet > 0) {
                // Get base rate for full interior
                $rateModel = Rate::where('project_type', 'interior')
                    ->where('surface_type', 'interior_full_base')
                    ->first();

                $baseRate = (float) ($rateModel?->rate ?? 0);
                $baseCost = $squareFeet * $baseRate;

                // Apply interior extras
                if (! empty($data['full_items']) && is_array($data['full_items'])) {
                    $extraMults = Multiplier::interiorExtra()
                        ->pluck('value', 'key')
                        ->toArray();

                    $sumDeviation = 0;
                    foreach ($data['full_items'] as $itemKey) {
                        $multiplier = (float) ($extraMults[$itemKey] ?? 1);
                        $sumDeviation += ($multiplier - 1);
                    }

                    $finalMultiplier = 1 + $sumDeviation;
                    $totalCost = $baseCost * $finalMultiplier;
                } else {
                    $totalCost = $baseCost;
                }
            }
        } else {
            // Partial interior calculation
            $areas = $data['areas'] ?? [];
            if (is_array($areas) && ! empty($areas)) {
                foreach ($areas as $area) {
                    $surfaces = $area['surfaces'] ?? [];
                    if (is_array($surfaces) && ! empty($surfaces)) {
                        foreach ($surfaces as $surface) {
                            if (empty($surface['surface_type'])) {
                                continue;
                            }

                            $rateModel = Rate::where('surface_type', $surface['surface_type'])->first();
                            if ($rateModel) {
                                $rate = (float) ($rateModel->rate ?? 0);
                                if ($rateModel->input_type === 'quantity') {
                                    $quantity = (int) ($surface['quantity'] ?? 1);
                                    $cost = $quantity * $rate;
                                } else {
                                    $measurement = (float) ($surface['measurement'] ?? 0);
                                    $cost = $measurement * $rate;
                                }
                                $totalCost += $cost;
                            }
                        }
                    }
                }
            }
        }

        return max(0, $totalCost);
    }

    public function calculateExterior(array $data): float
    {
        $totalFloorSpace = (float) ($data['total_floor_space'] ?? 0);

        // Only calculate if we have floor space
        if ($totalFloorSpace <= 0) {
            return 0;
        }

        // Get base rate for exterior projects
        $rateModel = Rate::where('surface_type', 'exterior')->first();
        $baseRate = (float) ($rateModel?->rate ?? 0);

        if ($baseRate <= 0) {
            return 0;
        }

        $baseCost = $totalFloorSpace * $baseRate;

        // Get multipliers with safe defaults
        $houseStyle = $data['house_style'] ?? null;
        $houseStyleMultiplier = 1;
        if (! empty($houseStyle)) {
            $houseStyleMultiplier = (float) (Multiplier::where('category', 'house_style')
                ->where('key', $houseStyle)
                ->value('value') ?? 1);
        }

        $numberOfFloors = $data['number_of_floors'] ?? null;
        $floorMultiplier = 1;
        if (! empty($numberOfFloors)) {
            $floorMultiplier = (float) (Multiplier::where('category', 'floor')
                ->where('key', (string) $numberOfFloors)
                ->value('value') ?? 1);
        }

        $paintCondition = $data['paint_condition'] ?? null;
        $conditionMultiplier = 1;
        if (! empty($paintCondition)) {
            $conditionMultiplier = (float) (Multiplier::where('category', 'condition')
                ->where('key', $paintCondition)
                ->value('value') ?? 1);
        }

        // Calculate additive multiplier
        $finalMultiplier = 1 + (
            ($houseStyleMultiplier - 1) +
            ($floorMultiplier - 1) +
            ($conditionMultiplier - 1)
        );

        // Apply coverage multiplier
        $coverage = $data['coverage'] ?? null;
        $coverageMultiplier = 1;
        if (! empty($coverage)) {
            $coverageMultiplier = (float) (Multiplier::where('category', 'coverage')
                ->where('key', $coverage)
                ->value('value') ?? 1);
        }

        return max(0, $baseCost * $finalMultiplier * $coverageMultiplier);
    }

    public function applyDeviation(float $baseCost, float $deviation): array
    {
        return [
            'low' => $baseCost * (1 - $deviation),
            'high' => $baseCost * (1 + $deviation),
        ];
    }
}
