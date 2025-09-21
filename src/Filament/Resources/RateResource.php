<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Fuelviews\SabHeroEstimator\Filament\Resources\RateResource\Pages;
use Fuelviews\SabHeroEstimator\Models\Rate;

class RateResource extends Resource
{
    protected static ?string $model = Rate::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Estimator';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('project_type')
                ->label('Project Type')
                ->options([
                    'interior' => 'Interior',
                    'exterior' => 'Exterior',
                ])
                ->required(),

            Forms\Components\TextInput::make('surface_type')
                ->label('Surface Type')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Forms\Components\TextInput::make('rate')
                ->label('Rate per Unit')
                ->numeric()
                ->required()
                ->step(0.01)
                ->prefix('$'),

            Forms\Components\Select::make('input_type')
                ->label('Input Type')
                ->options([
                    'measurement' => 'Square Footage (measurement)',
                    'quantity' => 'Quantity (count)',
                ])
                ->required(),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(3)
                ->maxLength(500),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project_type')
                    ->label('Project Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'interior' => 'success',
                        'exterior' => 'warning',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('surface_type')
                    ->label('Surface Type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rate')
                    ->label('Rate')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('input_type')
                    ->label('Input Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'measurement' => 'info',
                        'quantity' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('project_type')
                    ->options([
                        'interior' => 'Interior',
                        'exterior' => 'Exterior',
                    ]),
                SelectFilter::make('input_type')
                    ->options([
                        'measurement' => 'Measurement',
                        'quantity' => 'Quantity',
                    ]),
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
            'index' => Pages\ListRates::route('/'),
            'create' => Pages\CreateRate::route('/create'),
            'edit' => Pages\EditRate::route('/{record}/edit'),
        ];
    }
}
