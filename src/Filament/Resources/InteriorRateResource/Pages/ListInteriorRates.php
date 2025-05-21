<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources\InteriorRateResource\Pages;

use Fuelviews\SabHeroEstimator\Filament\Resources\InteriorRateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInteriorRates extends ListRecords
{
    protected static string $resource = InteriorRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
