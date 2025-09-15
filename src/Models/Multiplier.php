<?php

namespace Fuelviews\SabHeroEstimator\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Multiplier extends Model
{
    use HasFactory;

    public function getTable(): string
    {
        return config('sabhero-estimator.database.table_prefix').'multipliers';
    }

    protected $fillable = [
        'category',
        'key',
        'value',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
        ];
    }

    /**
     * Scope for house style multipliers
     */
    public function scopeHouseStyle($query)
    {
        return $query->where('category', 'house_style');
    }

    /**
     * Scope for floor multipliers
     */
    public function scopeFloor($query)
    {
        return $query->where('category', 'floor');
    }

    /**
     * Scope for condition multipliers
     */
    public function scopeCondition($query)
    {
        return $query->where('category', 'condition');
    }

    /**
     * Scope for coverage multipliers
     */
    public function scopeCoverage($query)
    {
        return $query->where('category', 'coverage');
    }

    /**
     * Scope for interior extra multipliers
     */
    public function scopeInteriorExtra($query)
    {
        return $query->where('category', 'interior_extra');
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
