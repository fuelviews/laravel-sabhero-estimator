<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources\MultiplierResource\Pages;

use Fuelviews\SabHeroEstimator\Filament\Resources\MultiplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
