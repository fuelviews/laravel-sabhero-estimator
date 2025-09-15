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

        if ($data['project_type'] === 'interior') {
            $baseCost = $this->calculateInterior($data);
        } else {
            $baseCost = $this->calculateExterior($data);
        }

        return $this->applyDeviation($baseCost, $deviation);
    }

    public function calculateInterior(array $data): float
    {
        $totalCost = 0;

        if ($data['interior_scope'] === 'full') {
            // Full interior calculation
            $squareFeet = $data['full_floor_space'] ?? $data['total_floor_space'] ?? 0;

            // Get base rate for full interior
            $rateModel = Rate::where('project_type', 'interior')
                ->where('surface_type', 'interior_full_base')
                ->first();

            $baseRate = $rateModel?->rate ?? 0;
            $baseCost = $squareFeet * $baseRate;

            // Apply interior extras
            if (! empty($data['full_items']) && is_array($data['full_items'])) {
                $extraMults = Multiplier::interiorExtra()
                    ->pluck('value', 'key')
                    ->toArray();

                $sumDeviation = 0;
                foreach ($data['full_items'] as $itemKey) {
                    $multiplier = $extraMults[$itemKey] ?? 1;
                    $sumDeviation += ($multiplier - 1);
                }

                $finalMultiplier = 1 + $sumDeviation;
                $totalCost = $baseCost * $finalMultiplier;
            } else {
                $totalCost = $baseCost;
            }
        } else {
            // Partial interior calculation
            foreach ($data['areas'] as $area) {
                foreach ($area['surfaces'] as $surface) {
                    $rateModel = Rate::where('surface_type', $surface['surface_type'])->first();
                    if ($rateModel) {
                        if ($rateModel->input_type === 'quantity') {
                            $cost = $surface['quantity'] * $rateModel->rate;
                        } else {
                            $cost = $surface['measurement'] * $rateModel->rate;
                        }
                        $totalCost += $cost;
                    }
                }
            }
        }

        return $totalCost;
    }

    public function calculateExterior(array $data): float
    {
        // Get base rate for exterior projects
        $rateModel = Rate::where('surface_type', 'exterior')->first();
        $baseCost = $rateModel ? ($data['total_floor_space'] * $rateModel->rate) : 0;

        // Get multipliers
        $houseStyleMultiplier = (float) Multiplier::where('category', 'house_style')
            ->where('key', $data['house_style'])
            ->value('value') ?? 1;

        $floorMultiplier = (float) Multiplier::where('category', 'floor')
            ->where('key', (string) $data['number_of_floors'])
            ->value('value') ?? 1;

        $conditionMultiplier = (float) Multiplier::where('category', 'condition')
            ->where('key', $data['paint_condition'])
            ->value('value') ?? 1;

        // Calculate additive multiplier
        $finalMultiplier = 1 + (
            ($houseStyleMultiplier - 1) +
            ($floorMultiplier - 1) +
            ($conditionMultiplier - 1)
        );

        // Apply coverage multiplier
        $coverageMultiplier = (float) Multiplier::where('category', 'coverage')
            ->where('key', $data['coverage'])
            ->value('value') ?? 1;

        return $baseCost * $finalMultiplier * $coverageMultiplier;
    }

    public function applyDeviation(float $baseCost, float $deviation): array
    {
        return [
            'low' => $baseCost * (1 - $deviation),
            'high' => $baseCost * (1 + $deviation),
        ];
    }
}
