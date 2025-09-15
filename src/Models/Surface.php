<?php

namespace Fuelviews\SabHeroEstimator\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Surface extends Model
{
    use HasFactory;

    public function getTable(): string
    {
        return config('sabhero-estimator.database.table_prefix').'surfaces';
    }

    protected $fillable = [
        'area_id',
        'surface_type',
        'measurement',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'area_id' => 'integer',
            'measurement' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    /**
     * Get the area this surface belongs to
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get the rate for this surface type
     */
    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class, 'surface_type', 'surface_type');
    }

    /**
     * Calculate cost for this surface
     */
    public function calculateCost(): float
    {
        $rate = Rate::where('surface_type', $this->surface_type)->first();

        if (! $rate) {
            return 0.0;
        }

        if ($rate->input_type === 'quantity') {
            return $this->quantity * $rate->rate;
        }

        return $this->measurement * $rate->rate;
    }

    /**
     * Get the input type for this surface
     */
    public function getInputTypeAttribute(): ?string
    {
        $rate = Rate::where('surface_type', $this->surface_type)->first();

        return $rate?->input_type;
    }
}
