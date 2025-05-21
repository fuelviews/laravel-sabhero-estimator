<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources;

use Fuelviews\SabHeroEstimator\Filament\Resources\MultiplierResource\Pages;
use Fuelviews\SabHeroEstimator\Models\Multiplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class MultiplierResource extends Resource
{
    protected static ?string $model = Multiplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Instant Estimator';
    protected static ?int    $navigationSort  = 4;

    // Filter out records with category "house_style"
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('category', '!=', 'house_style');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('category')
                ->required(),
            Forms\Components\TextInput::make('key')
                ->required(),
            Forms\Components\TextInput::make('value')
                ->numeric()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('category')->label('Category'),
            Tables\Columns\TextColumn::make('key')->label('Key'),
            Tables\Columns\TextColumn::make('value')->label('Multiplier'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMultipliers::route('/'),
            'create' => Pages\CreateMultiplier::route('/create'),
            'edit'   => Pages\EditMultiplier::route('/{record}/edit'),
        ];
    }
}
