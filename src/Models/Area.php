<?php

namespace Fuelviews\SabHeroEstimator\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use HasFactory;

    public function getTable(): string
    {
        return config('sabhero-estimator.database.table_prefix').'areas';
    }

    protected $fillable = [
        'project_id',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'project_id' => 'integer',
        ];
    }

    /**
     * Get the project this area belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the surfaces for this area
     */
    public function surfaces(): HasMany
    {
        return $this->hasMany(Surface::class);
    }

    /**
     * Get total square footage for this area
     */
    public function getTotalSquareFootageAttribute(): float
    {
        return $this->surfaces()
            ->whereNotNull('measurement')
            ->sum('measurement');
    }

    /**
     * Get total quantity for this area
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->surfaces()
            ->whereNotNull('quantity')
            ->sum('quantity');
    }
}
