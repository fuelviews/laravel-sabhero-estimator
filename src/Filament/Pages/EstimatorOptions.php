<?php

namespace Fuelviews\SabHeroEstimator\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Fuelviews\SabHeroEstimator\Models\Setting;

class EstimatorOptions extends Page
{
    use InteractsWithFormActions;

    protected static ?string $navigationGroup = 'Estimator';

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static ?string $navigationLabel = 'Estimator Options';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'sabhero-estimator::filament.pages.estimator-options';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function getRelativeRouteName(): string
    {
        return 'estimator-options';
    }

    public function mount(): void
    {
        $this->form->fill([
            'contact_info_order' => Setting::getValue('contact_info_order', 'first'),
            'interior_scope_show_choice' => Setting::getValue('interior_scope_show_choice', '1') !== '0',
            'interior_scope_default' => Setting::getValue('interior_scope_default', 'full'),
            'full_interior_assumption_label' => Setting::getValue('full_interior_assumption_label', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('contact_info_order')
                    ->label('Collect contact info')
                    ->options([
                        'first' => 'Before estimate',
                        'last' => 'After estimate',
                    ])
                    ->required()
                    ->native(false),
                Toggle::make('interior_scope_show_choice')
                    ->label('Let user choose full or partial interior')
                    ->default(true),
                Select::make('interior_scope_default')
                    ->label('Default interior scope (when choice is hidden)')
                    ->options([
                        'full' => 'Full interior',
                        'partial' => 'Partial interior',
                    ])
                    ->required()
                    ->native(false)
                    ->visible(fn ($get) => ! $get('interior_scope_show_choice')),
                TextInput::make('full_interior_assumption_label')
                    ->label('Full interior assumption note (optional)')
                    ->placeholder('This estimate assumes all or most walls are being painted.')
                    ->helperText('Shown in the full interior section. Leave blank to hide.')
                    ->maxLength(255),
            ])
            ->statePath('data');
    }

    /**
     * @return array<string, Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Select::make('contact_info_order')
                            ->label('Collect contact info')
                            ->options([
                                'first' => 'Before estimate',
                                'last' => 'After estimate',
                            ])
                            ->required()
                            ->native(false),
                        Toggle::make('interior_scope_show_choice')
                            ->label('Let user choose full or partial interior')
                            ->default(true),
                        Select::make('interior_scope_default')
                            ->label('Default interior scope (when choice is hidden)')
                            ->options([
                                'full' => 'Full interior',
                                'partial' => 'Partial interior',
                            ])
                            ->required()
                            ->native(false)
                            ->visible(fn ($get) => ! $get('interior_scope_show_choice')),
                        TextInput::make('full_interior_assumption_label')
                            ->label('Full interior assumption note (optional)')
                            ->placeholder('This estimate assumes all or most walls are being painted.')
                            ->helperText('Shown in the full interior section. Leave blank to hide.')
                            ->maxLength(255),
                    ])
                    ->statePath('data')
            ),
        ];
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::setValue('contact_info_order', $data['contact_info_order'] ?? 'first');
        Setting::setValue(
            'interior_scope_show_choice',
            (! empty($data['interior_scope_show_choice'])) ? '1' : '0'
        );
        Setting::setValue('interior_scope_default', $data['interior_scope_default'] ?? 'full');
        Setting::setValue('full_interior_assumption_label', $data['full_interior_assumption_label'] ?? '');

        Notification::make()
            ->title('Estimator options saved.')
            ->success()
            ->send();
    }
}
