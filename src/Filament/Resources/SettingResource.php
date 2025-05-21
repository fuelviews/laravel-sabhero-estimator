<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources;

use Fuelviews\SabHeroEstimator\Filament\Resources\SettingResource\Pages;
use Fuelviews\SabHeroEstimator\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon  = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'Instant Estimator';
    protected static ?string $navigationLabel = 'Estimator Settings';
    protected static ?int    $navigationSort  = 5;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('key')
                ->label('Setting Key')
                ->disabled()
                ->required(),
            TextInput::make('value')
                ->label('Setting Value')
                ->required(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('key')
                ->label('Setting')
                ->formatStateUsing(fn ($state) => Str::title(str_replace('_', ' ', $state))),
            TextColumn::make('value')
                ->label('Value'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'edit'  => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
