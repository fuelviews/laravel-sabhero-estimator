<?php

namespace Fuelviews\SabHeroEstimator\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Multiplier extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'house_style',
        'number_of_floors',
        'paint_condition',
        'multiplier',
        'key',
        'value',
        'category',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'multiplier' => 'decimal:2',
        'value' => 'decimal:2',
    ];

    /**
     * Register media collections for the model
     */
    public function registerMediaCollections(): void
    {
        // Get collection name from config or use default
        $collectionName = config('sabhero-estimator.collections.estimator_house_style_image', 'estimator_house_style_image');
        
        // Get disk from config or use default
        $disk = config('sabhero-estimator.media_disk', 'public');
        
        $this->addMediaCollection($collectionName)
            ->useDisk($disk)
            ->withResponsiveImages();
    }
}
