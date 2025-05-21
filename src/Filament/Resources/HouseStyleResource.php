<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources;

use Fuelviews\SabHeroEstimator\Filament\Resources\HouseStyleResource\Pages;
use Fuelviews\SabHeroEstimator\Models\Multiplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Tables\Table; // Import the correct Table class from Filament\Tables
use Illuminate\Database\Eloquent\Builder;

class HouseStyleResource extends Resource
{
    protected static ?string $model = Multiplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'Instant Estimator';
    protected static ?string $navigationLabel = 'Exterior House Styles';
    protected static ?int    $navigationSort  = 3;

    // Limit this resource to only records where category equals 'house_style'
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('category', 'house_style');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')
                ->label('House Style')
                ->required(),
            Forms\Components\TextInput::make('value')
                ->label('Multiplier')
                ->numeric()
                ->required(),
            Forms\Components\SpatieMediaLibraryFileUpload::make('house_style_image')
                ->label('House Style Image')
                ->collection('house_style_image')
                ->image()
                ->maxSize(1024),
            // Preset the category to "house_style" and disable editing.
            Forms\Components\TextInput::make('category')
                ->default('house_style')
                ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('key')->label('House Style'),
            TextColumn::make('value')->label('Multiplier'),
            Tables\Columns\SpatieMediaLibraryImageColumn::make('house_style_image')
                ->label('Image')
                ->collection('house_style_image'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListHouseStyles::route('/'),
            'create' => Pages\CreateHouseStyle::route('/create'),
            'edit'   => Pages\EditHouseStyle::route('/{record}/edit'),
        ];
    }
}
