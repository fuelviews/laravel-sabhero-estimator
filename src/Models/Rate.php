<?php

namespace Fuelviews\SabHeroEstimator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rate extends Model
{
    // If your table name isn't the pluralized version of the model name,
    // specify it explicitly:
        use HasFactory;

        protected $fillable = [
            'surface_type',
            'rate',
            'input_type',
        ];

    // Optionally, specify any fillable attributes:
    // protected $fillable = ['surface_type', 'rate'];
}
