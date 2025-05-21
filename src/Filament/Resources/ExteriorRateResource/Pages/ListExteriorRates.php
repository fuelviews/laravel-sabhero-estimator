<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources\ExteriorRateResource\Pages;

use Fuelviews\SabHeroEstimator\Filament\Resources\ExteriorRateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExteriorRates extends ListRecords
{
    protected static string $resource = ExteriorRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
