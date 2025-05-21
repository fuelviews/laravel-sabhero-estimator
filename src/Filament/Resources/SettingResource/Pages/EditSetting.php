<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources\SettingResource\Pages;

use Fuelviews\SabHeroEstimator\Filament\Resources\SettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
