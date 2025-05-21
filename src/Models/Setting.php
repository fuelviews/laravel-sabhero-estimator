<?php
namespace Fuelviews\SabHeroEstimator\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value', // Add this line if it's not already present
    ];

    /**
     * Retrieve the deviation percentage from the settings table.
     *
     * @return float
     */
    public static function getDeviationPercentage()
    {
        $setting = self::where('key', 'deviation_percentage')->first();
        return $setting ? ((float)$setting->value / 100) : 0.35;
    }
}
