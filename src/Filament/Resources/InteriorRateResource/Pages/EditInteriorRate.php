<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources\InteriorRateResource\Pages;

use Fuelviews\SabHeroEstimator\Filament\Resources\InteriorRateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInteriorRate extends EditRecord
{
    protected static string $resource = InteriorRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
