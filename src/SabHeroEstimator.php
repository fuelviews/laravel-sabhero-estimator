<?php

namespace Fuelviews\SabHeroEstimator;

use Fuelviews\SabHeroEstimator\Models\Multiplier;
use Fuelviews\SabHeroEstimator\Models\Rate;
use Fuelviews\SabHeroEstimator\Models\Setting;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Get the full URL for an image stored in the configured disk
     *
     * @param  string|null  $imagePath  The relative path to the image
     * @return string|null The full URL to the image or null if no path provided
     */
    public function getImageUrl(?string $imagePath): ?string
    {
        if (! $imagePath) {
            return null;
        }

        $disk = config('sabhero-estimator.media.disk', 'public');

        try {
            return Storage::disk($disk)->url($imagePath);
        } catch (\Exception $e) {
            // Fall back to asset() if Storage fails
            return asset($imagePath);
        }
    }

    /**
     * Copy images from package to configured disk
     *
     * @return bool
     */
    public function publishImages(): bool
    {
        $sourceDir = __DIR__.'/../resources/images';
        $disk = config('sabhero-estimator.media.disk', 'public');
        $path = 'estimator/images';

        if (! file_exists($sourceDir)) {
            return false;
        }

        $files = scandir($sourceDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $sourcePath = $sourceDir . '/' . $file;
            $targetPath = $path . '/' . $file;

            if (is_file($sourcePath)) {
                Storage::disk($disk)->put(
                    $targetPath,
                    file_get_contents($sourcePath)
                );
            }
        }

        return true;
    }
}
