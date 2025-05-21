<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources\EstimatorSettingResource\Pages;

use Fuelviews\SabHeroEstimator\Filament\Resources\EstimatorSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSettings extends ListRecords
{
    protected static string $resource = EstimatorSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
