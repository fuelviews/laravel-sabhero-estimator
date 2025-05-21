<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources;

use Fuelviews\SabHeroEstimator\Filament\Resources\ExteriorRateResource\Pages;
use Fuelviews\SabHeroEstimator\Models\Rate;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;


class ExteriorRateResource extends Resource
{
    protected static ?string $model = \Fuelviews\SabHeroEstimator\Models\Rate::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    // Group Rates under "Instant Estimator"
    protected static ?string $navigationGroup = 'Instant Estimator';
    protected static ?string $navigationLabel = 'Exterior Rates';
    protected static ?int    $navigationSort  = 2;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('surface_type')
                ->required(),
            TextInput::make('rate')
                ->numeric()
                ->required(),
            Select::make('input_type')
                ->options([
                    'measurement' => 'Measurement',
                    'quantity'    => 'Quantity',
                ])
                ->required(),
            TextInput::make('project_type')
                ->default('exterior')
                ->disabled(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('surface_type')->label('Surface'),
            TextColumn::make('rate')->label('Rate'),
            TextColumn::make('input_type')->label('Input Type'),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('project_type', 'exterior');  // or 'exterior' in ExteriorRateResource
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExteriorRates::route('/'),
            'create' => Pages\CreateExteriorRate::route('/create'),
            'edit'   => Pages\EditExteriorRate::route('/{record}/edit'),
        ];
    }
}
