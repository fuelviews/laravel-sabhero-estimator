<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources\EstimatorSettingResource\Pages;

use Fuelviews\SabHeroEstimator\Filament\Resources\EstimatorSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSetting extends EditRecord
{
    protected static string $resource = EstimatorSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
