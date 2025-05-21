<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources\ExteriorRateResource\Pages;

use Fuelviews\SabHeroEstimator\Filament\Resources\ExteriorRateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExteriorRate extends EditRecord
{
    protected static string $resource = ExteriorRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
