<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Fuelviews\SabHeroEstimator\Filament\Resources\MultiplierResource\Pages;
use Fuelviews\SabHeroEstimator\Models\Multiplier;
use Illuminate\Database\Eloquent\Builder;

class MultiplierResource extends Resource
{
    protected static ?string $model = Multiplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Estimator';

    protected static ?int $navigationSort = 4;

    protected static function categoryOptions(): array
    {
        return [
            'house_style' => 'Exterior House Style',
            'floor' => 'Number of Floors',
            'condition' => 'Paint Condition',
            'coverage' => 'Coverage %',
            'interior_extra' => 'Interior Extras',
        ];
    }

    // Filter out records with category "house_style"
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('category', '!=', 'house_style');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('category')
                ->label('Category')
                ->options(self::categoryOptions())
                ->required(),

            Forms\Components\TextInput::make('key')
                ->label('Key / Slug')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('value')
                ->label('Multiplier')
                ->numeric()
                ->required()
                ->step(0.01),

            Forms\Components\FileUpload::make('image')
                ->label('Image (Optional)')
                ->image()
                ->directory('estimator/multipliers'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->formatStateUsing(fn ($state) => self::categoryOptions()[$state] ?? $state)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Multiplier')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(fn () => null),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options(self::categoryOptions())
                    ->label('Category'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMultipliers::route('/'),
            'create' => Pages\CreateMultiplier::route('/create'),
            'edit' => Pages\EditMultiplier::route('/{record}/edit'),
        ];
    }
}
