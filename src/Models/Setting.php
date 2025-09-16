<?php

namespace Fuelviews\SabHeroEstimator\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    public function getTable(): string
    {
        return config('sabhero-estimator.table.prefix').'settings';
    }

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Retrieve the deviation percentage from the settings table.
     */
    public static function getDeviationPercentage(): float
    {
        $setting = self::where('key', 'deviation_percentage')->first();

        return $setting ? ((float) $setting->value / 100) : 0.35; // 35% default
    }

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();

        return $setting?->value ?? $default;
    }

    /**
     * Set a setting value by key
     */
    public static function setValue(string $key, mixed $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Scope by key
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }
}
