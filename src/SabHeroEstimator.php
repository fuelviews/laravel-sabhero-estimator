<?php

namespace Fuelviews\SabHeroEstimator;

use Fuelviews\SabHeroEstimator\Models\Multiplier;
use Fuelviews\SabHeroEstimator\Models\Rate;
use Fuelviews\SabHeroEstimator\Models\Setting;

class SabHeroEstimator
{
    /**
     * Get all available surface types for a project type
     */
    public function getSurfaceTypes(?string $projectType = null, ?string $scope = null): array
    {
        $query = Rate::query();

        if ($projectType) {
            $query->where('project_type', $projectType);
        }

        // Filter out full interior base when doing partial interior
        if ($projectType === 'interior' && $scope === 'partial') {
            $query->where('surface_type', '!=', 'interior_full_base');
        }

        return $query->orderBy('surface_type')
            ->pluck('surface_type', 'surface_type')
            ->toArray();
    }

    /**
     * Get multipliers by category
     */
    public function getMultipliersByCategory(string $category): array
    {
        return Multiplier::where('category', $category)
            ->orderBy('key')
            ->get()
            ->toArray();
    }

    /**
     * Get house styles with images
     */
    public function getHouseStyles(): array
    {
        return Multiplier::houseStyle()
            ->distinct()
            ->get(['key', 'image'])
            ->toArray();
    }

    /**
     * Get floor options
     */
    public function getFloorOptions(): array
    {
        return Multiplier::floor()
            ->orderBy('key')
            ->pluck('key')
            ->toArray();
    }

    /**
     * Get paint condition options
     */
    public function getPaintConditionOptions(): array
    {
        return Multiplier::condition()
            ->orderBy('key')
            ->get(['key', 'value'])
            ->toArray();
    }

    /**
     * Get coverage options
     */
    public function getCoverageOptions(): array
    {
        return Multiplier::coverage()
            ->orderBy('value', 'desc')
            ->get(['key', 'value'])
            ->toArray();
    }

    /**
     * Get interior full items (extras)
     */
    public function getInteriorFullItems(): array
    {
        return Multiplier::interiorExtra()
            ->orderBy('key')
            ->pluck('key', 'key')
            ->toArray();
    }

    /**
     * Get setting value
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return Setting::getValue($key, $default);
    }

    /**
     * Set setting value
     */
    public function setSetting(string $key, mixed $value): void
    {
        Setting::setValue($key, $value);
    }

    /**
     * Get deviation percentage
     */
    public function getDeviationPercentage(): float
    {
        return Setting::getDeviationPercentage();
    }
}
