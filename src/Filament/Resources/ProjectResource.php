<?php

namespace Fuelviews\SabHeroEstimator\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Fuelviews\SabHeroEstimator\Filament\Resources\ProjectResource\Pages;
use Fuelviews\SabHeroEstimator\Models\Project;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Estimator';

    protected static ?int $navigationSort = 1;

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

            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('phone')
                ->tel()
                ->required()
                ->maxLength(50),

            Forms\Components\Textarea::make('address')
                ->required()
                ->rows(2)
                ->maxLength(255),

            Forms\Components\TextInput::make('estimated_low')
                ->label('Estimated Low')
                ->numeric()
                ->prefix('$')
                ->step(0.01),

            Forms\Components\TextInput::make('estimated_high')
                ->label('Estimated High')
                ->numeric()
                ->prefix('$')
                ->step(0.01),

            Forms\Components\KeyValue::make('exterior_details')
                ->label('Exterior Details')
                ->keyLabel('Property')
                ->valueLabel('Value')
                ->visible(fn (Forms\Get $get): bool => $get('project_type') === 'exterior'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'interior' => 'success',
                        'exterior' => 'warning',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Phone copied!'),

                Tables\Columns\TextColumn::make('estimated_low')
                    ->label('Est. Range')
                    ->formatStateUsing(function (Project $record): string {
                        return $record->estimate_range;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('project_type')
                    ->options([
                        'interior' => 'Interior',
                        'exterior' => 'Exterior',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Contact Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('email')
                            ->copyable()
                            ->copyMessage('Email copied!'),
                        Infolists\Components\TextEntry::make('phone')
                            ->copyable()
                            ->copyMessage('Phone copied!'),
                        Infolists\Components\TextEntry::make('address'),
                    ])->columns(2),

                Infolists\Components\Section::make('Project Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('project_type')
                            ->label('Project Type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'interior' => 'success',
                                'exterior' => 'warning',
                            }),
                        Infolists\Components\TextEntry::make('estimated_low')
                            ->label('Estimate Range')
                            ->formatStateUsing(function (Project $record): string {
                                return $record->estimate_range;
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Submitted At')
                            ->dateTime(),
                    ])->columns(3),

                Infolists\Components\Section::make('Exterior Details')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('exterior_details')
                            ->label('')
                            ->keyLabel('Property')
                            ->valueLabel('Value'),
                    ])
                    ->visible(fn (Project $record): bool => $record->project_type === 'exterior'),

                Infolists\Components\Section::make('Areas & Surfaces')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('areas')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Area Name'),
                                Infolists\Components\RepeatableEntry::make('surfaces')
                                    ->label('Surfaces')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('surface_type')
                                            ->label('Surface Type'),
                                        Infolists\Components\TextEntry::make('measurement')
                                            ->label('Sq Ft')
                                            ->suffix(' sq ft')
                                            ->visible(fn ($state): bool => $state !== null),
                                        Infolists\Components\TextEntry::make('quantity')
                                            ->label('Quantity')
                                            ->visible(fn ($state): bool => $state > 1),
                                    ])
                                    ->columns(3),
                            ]),
                    ])
                    ->visible(fn (Project $record): bool => $record->project_type === 'interior' && $record->areas->count() > 0),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
