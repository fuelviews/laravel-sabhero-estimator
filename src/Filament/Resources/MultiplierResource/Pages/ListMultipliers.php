<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources\MultiplierResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Fuelviews\SabHeroEstimator\Filament\Resources\MultiplierResource;

class ListMultipliers extends ListRecords
{
    protected static string $resource = MultiplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
