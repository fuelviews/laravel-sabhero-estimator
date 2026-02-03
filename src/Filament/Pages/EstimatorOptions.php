<?php

namespace Fuelviews\SabHeroEstimator\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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

        Notification::make()
            ->title('Estimator options saved.')
            ->success()
            ->send();
    }
}
