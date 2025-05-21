<?php
namespace Fuelviews\SabHeroEstimator\Models;
use Illuminate\Database\Eloquent\Model;
class Surface extends Model
{
    protected $fillable = [
        'area_id',
        'surface_type',
        'measurement',
        'quantity',
    ];
}
