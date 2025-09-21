<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources\MultiplierResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Fuelviews\SabHeroEstimator\Filament\Resources\MultiplierResource;

class EditMultiplier extends EditRecord
{
    protected static string $resource = MultiplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
