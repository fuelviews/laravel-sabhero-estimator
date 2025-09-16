<?php

namespace Fuelviews\SabHeroEstimator\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    public function getTable(): string
    {
        return config('sabhero-estimator.table.prefix').'rates';
    }

    protected $fillable = [
        'surface_type',
        'rate',
        'input_type',
        'description',
        'project_type',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
        ];
    }

    /**
     * Scope for interior rates only
     */
    public function scopeInterior($query)
    {
        return $query->where('project_type', 'interior');
    }

    /**
     * Scope for exterior rates only
     */
    public function scopeExterior($query)
    {
        return $query->where('project_type', 'exterior');
    }

    /**
     * Get rates for a specific surface type
     */
    public function scopeBySurfaceType($query, string $surfaceType)
    {
        return $query->where('surface_type', $surfaceType);
    }
}
