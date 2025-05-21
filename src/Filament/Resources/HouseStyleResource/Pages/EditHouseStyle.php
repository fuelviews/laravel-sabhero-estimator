<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources\HouseStyleResource\Pages;

use Fuelviews\SabHeroEstimator\Filament\Resources\HouseStyleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHouseStyle extends EditRecord
{
    protected static string $resource = HouseStyleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
