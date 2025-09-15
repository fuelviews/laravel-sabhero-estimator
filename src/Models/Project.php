<?php

namespace Fuelviews\SabHeroEstimator\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    public function getTable(): string
    {
        return config('sabhero-estimator.database.table_prefix').'projects';
    }

    protected $fillable = [
        'project_type',
        'name',
        'email',
        'phone',
        'address',
        'estimated_low',
        'estimated_high',
        'exterior_details',
    ];

    protected function casts(): array
    {
        return [
            'estimated_low' => 'decimal:2',
            'estimated_high' => 'decimal:2',
            'exterior_details' => 'json',
        ];
    }

    /**
     * Get the areas for this project
     */
    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    /**
     * Scope for interior projects
     */
    public function scopeInterior($query)
    {
        return $query->where('project_type', 'interior');
    }

    /**
     * Scope for exterior projects
     */
    public function scopeExterior($query)
    {
        return $query->where('project_type', 'exterior');
    }

    /**
     * Get formatted estimate range
     */
    public function getEstimateRangeAttribute(): string
    {
        $symbol = config('sabhero-estimator.defaults.currency_symbol', '$');
        $places = config('sabhero-estimator.defaults.decimal_places', 2);

        return $symbol.number_format($this->estimated_low, $places).' - '.
               $symbol.number_format($this->estimated_high, $places);
    }
}
