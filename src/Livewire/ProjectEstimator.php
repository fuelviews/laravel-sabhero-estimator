<?php

namespace Fuelviews\SabHeroEstimator\Livewire;

use Fuelviews\SabHeroEstimator\Models\Area;
use Fuelviews\SabHeroEstimator\Models\Multiplier;
use Fuelviews\SabHeroEstimator\Models\Project;
use Fuelviews\SabHeroEstimator\Models\Rate;
use Fuelviews\SabHeroEstimator\Models\Setting;
use Fuelviews\SabHeroEstimator\Models\Surface;
use Fuelviews\SabHeroEstimator\Services\CalculationService;
use Fuelviews\SabHeroEstimator\Services\FormSubmissionService;
use Livewire\Component;

class ProjectEstimator extends Component
{
    // Form endpoint
    public $formEndpoint;

    public $step = 1;

    // Step 1 – Project Type & Contact Info
    public $project_type;

    public string $first_name = '';

    public string $last_name = '';

    public $email;

    public $phone;

    public $zipCode;

    // Step 2 – Details for measurements
    public $areas = [];

    // Exterior-specific fields
    public $houseStyles = [];

    public $house_style;

    public $selectHouseStyleLabel;

    public $number_of_floors;

    public $floorOptions = [];

    public $total_floor_space;

    public $paint_condition;

    public $paintConditionOptions = [];

    public $paintConditionLabel;

    public $paintConditionDefaultOption;

    public $coverageOptions = [];

    public $coverage = 'The Entire House';

    public $coverageLabel;

    public $numberOfFloorsLabel;

    public $numberOfFloorsDefaultOption;

    public $surfaceTypes = [];

    public $selectedSurfaceType;

    // Flag to control when contact step errors should be shown.
    public bool $touchedContact = false;

    // When true, contact is collected on step 2 (before measurements). When false, contact is on step 5 (after review).
    public bool $contactInfoFirst = true;

    // When true, user chooses full vs partial interior. When false, interior_scope is set from interiorScopeDefault.
    public bool $interiorScopeShowChoice = true;

    public string $interiorScopeDefault = 'full';

    // Calculated estimates
    public $estimated_low;

    public $estimated_high;

    // Interior scope (Full vs Partial)
    public ?string $interior_scope = null;

    public ?float $full_floor_space = null;

    public array $full_items = [];

    public array $interiorFullItems = [];

    /** Optional note shown in full interior section (e.g. assumes all/most walls). Empty = hidden. */
    public string $fullInteriorAssumptionLabel = '';

    // Define which field should be displayed for each surface type.
    public $surfaceInputMapping = [
        'interior_wall' => 'measurement',
        'door' => 'quantity',
        'window' => 'quantity',
    ];

    protected CalculationService $calculationService;

    protected FormSubmissionService $formSubmissionService;

    public function boot(CalculationService $calculationService, FormSubmissionService $formSubmissionService)
    {
        $this->calculationService = $calculationService;
        $this->formSubmissionService = $formSubmissionService;
    }

    public function mount()
    {
        $this->loadConfiguration();
        $this->initializeDefaults();
    }

    protected function loadConfiguration()
    {
        // Load condition options from the multipliers table (category 'condition')
        $this->paintConditionOptions = Multiplier::condition()
            ->orderBy('key')
            ->get(['key', 'value'])
            ->toArray();

        // Load condition label and default option from settings
        $this->paintConditionLabel = Setting::getValue('paint_condition_label', 'Condition of Existing Paint');
        $this->paintConditionDefaultOption = Setting::getValue('paint_condition_default_option', 'Select condition');

        // Load the coverage label setting
        $this->coverageLabel = Setting::getValue('coverage_label', 'How much of the house is being painted?');

        // Build the surface types list from Rates, excluding the full-interior base when needed
        $this->loadSurfaceTypes();

        // Optionally, set a default selection if needed
        if (empty($this->selectedSurfaceType) && ! empty($this->surfaceTypes)) {
            $this->selectedSurfaceType = array_key_first($this->surfaceTypes);
        }

        // Load the endpoint
        $this->formEndpoint = app()->environment('production')
            ? config('sabhero-estimator.form_endpoints.production_url')
            : config('sabhero-estimator.form_endpoints.development_url');

        // Initialize with one default area and one default surface
        $this->areas = [
            ['name' => '', 'surfaces' => [
                ['surface_type' => '', 'measurement' => null, 'quantity' => 1],
            ]],
        ];

        // Retrieve house style records with their key and image
        $this->houseStyles = Multiplier::houseStyle()
            ->distinct()
            ->get(['key', 'image'])
            ->toArray();

        // Load the "Select House Style" label from the settings table
        $this->selectHouseStyleLabel = Setting::getValue('select_house_style_label', 'Select House Style:');

        // Load available floor options from the multipliers table where category is "floor"
        $this->floorOptions = Multiplier::floor()
            ->orderBy('key')
            ->pluck('key')
            ->toArray();

        // Set default value if none is provided
        if (empty($this->number_of_floors) && ! empty($this->floorOptions)) {
            $this->number_of_floors = $this->floorOptions[0];
        }

        // Load settings for the floors label and default option
        $this->numberOfFloorsLabel = Setting::getValue('number_of_floors_label', 'Number of Floors');
        $this->numberOfFloorsDefaultOption = Setting::getValue('number_of_floors_default_option', 'Select the number of floors');

        // Load coverage options as an array from the multipliers table
        $this->coverageOptions = Multiplier::coverage()
            ->orderBy('value', 'desc')
            ->get(['key', 'value'])
            ->toArray();

        // Set default value if not already set
        if (! $this->coverage) {
            $this->coverage = 'The Entire House';
        }

        // Load interior extras (admin-editable) from multipliers table
        $this->interiorFullItems = Multiplier::interiorExtra()
            ->orderBy('key')
            ->pluck('key', 'key')
            ->toArray();

        // Initialize interior scope vars
        $this->interiorScopeShowChoice = Setting::getValue('interior_scope_show_choice', '1') !== '0';
        $this->interiorScopeDefault = Setting::getValue('interior_scope_default', 'full');
        $this->interior_scope = null;
        $this->full_floor_space = null;
        $this->full_items = [];
        if (! $this->interiorScopeShowChoice) {
            $this->interior_scope = $this->interiorScopeDefault;
        }

        // Contact info order: "first" = collect contact on step 2, "last" = collect contact on step 5
        $this->contactInfoFirst = Setting::getValue('contact_info_order', 'first') === 'first';

        $this->fullInteriorAssumptionLabel = (string) Setting::getValue('full_interior_assumption_label', '');
    }

    // Return true only if there is at least one real paint_condition option
    protected function hasPaintConditionOptions(): bool
    {
        return ! empty($this->paintConditionOptions)
            && collect($this->paintConditionOptions)
                ->filter(fn ($o) => ! empty($o['key']))
                ->count() > 0;
    }

    protected function initializeDefaults()
    {
        // Any additional initialization can go here
    }

    // Add a new area (e.g., a new room)
    public function addArea()
    {
        $this->areas[] = ['name' => '', 'surfaces' => [
            ['surface_type' => '', 'measurement' => null, 'quantity' => 1],
        ]];
    }

    // Add a new surface to an area
    public function addSurface($areaIndex)
    {
        $this->areas[$areaIndex]['surfaces'][] = ['surface_type' => '', 'measurement' => null, 'quantity' => 1];
    }

    public function removeArea($index)
    {
        // Only remove if more than one area exists
        if (count($this->areas) > 1) {
            unset($this->areas[$index]);
            // Reindex the array so that Livewire can properly track the keys.
            $this->areas = array_values($this->areas);
        }
    }

    public function removeSurface($areaIndex, $surfaceIndex)
    {
        // Check that the area exists and has surfaces
        // Only allow removal if there's more than one surface (keep at least one)
        if (isset($this->areas[$areaIndex]['surfaces']) && isset($this->areas[$areaIndex]['surfaces'][$surfaceIndex]) && count($this->areas[$areaIndex]['surfaces']) > 1) {
            unset($this->areas[$areaIndex]['surfaces'][$surfaceIndex]);
            // Reindex the surfaces array for the area
            $this->areas[$areaIndex]['surfaces'] = array_values($this->areas[$areaIndex]['surfaces']);
        }
    }

    /**
     * Load available surface types from the rates table, with context-aware filtering.
     */
    protected function loadSurfaceTypes(): void
    {
        $query = Rate::query();

        if ($this->project_type) {
            $query->where('project_type', $this->project_type);
        }

        // When doing interior partial mode, hide the base full-interior surface
        if ($this->project_type === 'interior' && $this->interior_scope === 'partial') {
            $query->where('surface_type', '!=', 'interior_full_base');
        }

        $this->surfaceTypes = $query
            ->orderBy('surface_type')
            ->pluck('surface_type', 'surface_type')
            ->toArray();
    }

    // Navigation methods
    public function previousStep()
    {
        // Clear errors so we don't show stale messages
        $this->resetValidation();

        if (! $this->contactInfoFirst) {
            // Contact last: 5 steps (step 4 = contact only, step 5 = review + submit)
            if ($this->step === 5) {
                $this->step = 4;
                return;
            }
            if ($this->step === 4) {
                $this->step = 3;
                return;
            }
            if ($this->step === 3) {
                $this->step = 2;
                return;
            }
            if ($this->step === 2) {
                $this->step = 1;
                return;
            }
            return;
        }

        // Contact first: 4 steps
        if ($this->step === 4) {
            $this->step = 3;
            return;
        }
        if ($this->step === 3) {
            $this->touchedContact = false;
            $this->step = 2;
            return;
        }
        if ($this->step === 2) {
            $this->step = 1;
            return;
        }
    }

    public function nextStep()
    {
        // Clear any previous validation/errors before moving
        $this->resetValidation();

        if (! $this->contactInfoFirst) {
            // Contact last: step 1→2, 2→3, 3→4 (with calculate; step 4 = contact + price + submit)
            if ($this->step === 1) {
                $this->step = 2;
                return;
            }
            if ($this->step === 2) {
                $this->validateStep(2);
                $this->step = 3;
                return;
            }
            if ($this->step === 3) {
                $this->validateStep(3);
                $this->calculateEstimate();
                $this->step = 4;
                return;
            }
            return;
        }

        // Contact first: step 1→2, 2→3 (step 3→4 is via submitProject in view)
        if ($this->step === 1) {
            $this->step = 2;
            return;
        }
        if ($this->step === 2) {
            $this->touchedContact = true;
            $this->validateStep(2);
            $this->step = 3;
            return;
        }
        if ($this->step === 3) {
            $this->validateStep(3);
            $this->calculateEstimate();
            $this->step = 4;
            return;
        }
    }

    /**
     * When contact is collected last: validate step 3, calculate estimate, advance to step 4 (contact).
     */
    public function calculateAndContinue(): void
    {
        $this->resetValidation();
        $this->validateStep(3);
        $this->calculateEstimate();
        $this->step = 4;
    }

    /**
     * When contact is collected last: validate step 4 (contact), then advance to step 5 to show estimate.
     */
    public function contactAndShowEstimate(): void
    {
        $this->resetValidation();
        $this->validateStep(4);
        $this->step = 5;
    }

    // Validation for each step
    public function validateStep($step)
    {
        if ($step == 2) {
            if ($this->contactInfoFirst) {
                $this->validate([
                    'project_type' => 'required|in:interior,exterior',
                    'first_name' => 'required|string|max:25',
                    'last_name' => 'required|string|max:25',
                    'email' => 'required|email|max:65',
                    'phone' => 'required|string|max:50',
                    'zipCode' => 'required|string|max:12',
                ]);
            } else {
                $this->validate([
                    'project_type' => 'required|in:interior,exterior',
                ]);
            }
        }

        if ($step == 4 && ! $this->contactInfoFirst) {
            $this->touchedContact = true;
            $this->validate([
                'first_name' => 'required|string|max:25',
                'last_name' => 'required|string|max:25',
                'email' => 'required|email|max:65',
                'phone' => 'required|string|max:50',
                'zipCode' => 'required|string|max:12',
            ]);
        }

        if ($step == 5) {
            $this->touchedContact = true;
            $this->validate([
                'first_name' => 'required|string|max:25',
                'last_name' => 'required|string|max:25',
                'email' => 'required|email|max:65',
                'phone' => 'required|string|max:50',
                'zipCode' => 'required|string|max:12',
            ]);
        }

        if ($step == 3) {
            if ($this->project_type === 'interior') {
                $rules = [];
                $messages = [];

                // Require choosing full vs partial (rooms)
                $rules['interior_scope'] = 'required|in:full,partial';
                $messages['interior_scope.required'] = 'Please choose full interior or individual rooms.';
                $messages['interior_scope.in'] = 'Invalid interior selection.';

                if ($this->interior_scope === 'full') {
                    // Full interior: require total floor space
                    $rules['full_floor_space'] = 'required|numeric|min:0';
                    $messages['full_floor_space.required'] = 'Please enter the total interior floor space.';
                    $messages['full_floor_space.numeric'] = 'Floor space must be a number.';
                    $messages['full_floor_space.min'] = 'Floor space cannot be negative.';
                } else {
                    // Partial / rooms: validate each surface like before
                    foreach ($this->areas as $i => $area) {
                        foreach ($area['surfaces'] as $j => $surface) {
                            $fieldTypeKey = "areas.$i.surfaces.$j.surface_type";
                            $rules[$fieldTypeKey] = 'required|in:'.implode(',', array_keys($this->surfaceTypes));
                            $messages["{$fieldTypeKey}.required"] = 'At least one surface is required.';

                            $inputType = Rate::where('surface_type', $surface['surface_type'])
                                ->value('input_type');

                            if ($inputType === 'measurement') {
                                $fieldMeasureKey = "areas.$i.surfaces.$j.measurement";
                                $rules[$fieldMeasureKey] = 'required|numeric|min:0';
                                $messages["{$fieldMeasureKey}.required"] = 'Please enter a square-footage value.';
                            } elseif ($inputType === 'quantity') {
                                $fieldQtyKey = "areas.$i.surfaces.$j.quantity";
                                $rules[$fieldQtyKey] = 'required|integer|min:1';
                                $messages["{$fieldQtyKey}.required"] = 'Please enter a quantity.';
                            }
                        }
                    }
                }

                $this->validate($rules, $messages);
            } else {
                // Exterior
                $hasPaintCondition = $this->hasPaintConditionOptions();

                // If there are no valid options, ensure the value is cleared so it doesn't trip validation or get saved accidentally.
                if (! $hasPaintCondition) {
                    $this->paint_condition = null;
                }

                $rules = [
                    'house_style' => 'required|string',
                    'number_of_floors' => 'required|integer|min:1',
                    'total_floor_space' => 'required|numeric|min:0',
                    'paint_condition' => $hasPaintCondition ? 'required|string' : 'nullable|string',
                    'coverage' => 'required|string',
                ];

                $messages = [
                    'house_style.required' => 'Please choose a house style.',
                    'number_of_floors.required' => 'Please select the number of floors.',
                    'number_of_floors.integer' => 'Number of floors must be a whole number.',
                    'number_of_floors.min' => 'Number of floors must be at least 1.',
                    'total_floor_space.required' => 'Please enter the total floor space.',
                    'total_floor_space.numeric' => 'Floor space must be a number.',
                    'total_floor_space.min' => 'Floor space must be at least zero.',
                    'coverage.required' => 'Please select how much of the house is being painted.',
                ];

                // Only add this message when the field is actually required
                if ($hasPaintCondition) {
                    $messages['paint_condition.required'] = 'Please select the condition of the existing paint.';
                }

                $this->validate($rules, $messages);
            }
        }
    }

    public function calculateEstimate()
    {
        // Only calculate if we have minimum required data
        if (! $this->hasMinimumDataForCalculation()) {
            $this->estimated_low = 0;
            $this->estimated_high = 0;

            return;
        }

        $data = $this->getCalculationData();
        $result = $this->calculationService->calculate($data);

        $this->estimated_low = max(0, $result['low'] ?? 0);
        $this->estimated_high = max(0, $result['high'] ?? 0);
    }

    /**
     * Check if we have minimum required data to perform a calculation
     */
    protected function hasMinimumDataForCalculation(): bool
    {
        if (empty($this->project_type)) {
            return false;
        }

        if ($this->project_type === 'interior') {
            if (empty($this->interior_scope)) {
                return false;
            }

            if ($this->interior_scope === 'full') {
                // Need floor space for full interior
                return ! empty($this->full_floor_space) && $this->full_floor_space > 0;
            } else {
                // Need at least one area with surfaces for partial
                if (empty($this->areas) || ! is_array($this->areas)) {
                    return false;
                }

                foreach ($this->areas as $area) {
                    $surfaces = $area['surfaces'] ?? [];
                    if (! empty($surfaces) && is_array($surfaces)) {
                        foreach ($surfaces as $surface) {
                            if (! empty($surface['surface_type'])) {
                                // Check if we have the required input (measurement or quantity)
                                $inputType = Rate::where('surface_type', $surface['surface_type'])->value('input_type');
                                if ($inputType === 'quantity') {
                                    if (! empty($surface['quantity']) && $surface['quantity'] > 0) {
                                        return true;
                                    }
                                } else {
                                    if (! empty($surface['measurement']) && $surface['measurement'] > 0) {
                                        return true;
                                    }
                                }
                            }
                        }
                    }
                }

                return false;
            }
        } else {
            // Exterior: need floor space at minimum
            return ! empty($this->total_floor_space) && $this->total_floor_space > 0;
        }
    }

    protected function getCalculationData(): array
    {
        $data = [
            'project_type' => $this->project_type,
            'areas' => $this->areas,
            'interior_scope' => $this->interior_scope,
            'full_floor_space' => $this->full_floor_space,
            'full_items' => $this->full_items,
            'house_style' => $this->house_style,
            'number_of_floors' => $this->number_of_floors,
            'total_floor_space' => $this->total_floor_space,
            'paint_condition' => $this->paint_condition,
            'coverage' => $this->coverage,
        ];

        return $data;
    }

    // Final submission: calculate, save, and send to API
    public function submitProject()
    {
        $this->validateStep($this->step);
        $this->calculateEstimate();

        // Save the project data
        $project = Project::create([
            'project_type' => $this->project_type,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'zipCode' => $this->zipCode,
            'estimated_low' => $this->estimated_low,
            'estimated_high' => $this->estimated_high,
        ]);

        if ($this->project_type === 'interior') {
            // Save interior details
            $interiorDetails = [
                'interior_scope' => $this->interior_scope,
            ];

            if ($this->interior_scope === 'full') {
                $interiorDetails['full_floor_space'] = $this->full_floor_space;
                $interiorDetails['full_items'] = $this->full_items;
            }

            $project->update([
                'interior_details' => $interiorDetails,
            ]);

            // Only save areas/surfaces for partial interior projects
            if ($this->interior_scope === 'partial') {
                foreach ($this->areas as $areaData) {
                    $area = Area::create([
                        'project_id' => $project->id,
                        'name' => $areaData['name'] ?? null,
                    ]);
                    foreach ($areaData['surfaces'] as $surfaceData) {
                        Surface::create([
                            'area_id' => $area->id,
                            'surface_type' => $surfaceData['surface_type'],
                            'measurement' => $surfaceData['measurement'],
                            'quantity' => $surfaceData['quantity'],
                        ]);
                    }
                }
            }
        } else {
            $project->update([
                'exterior_details' => [
                    'house_style' => $this->house_style,
                    'number_of_floors' => $this->number_of_floors,
                    'total_floor_space' => $this->total_floor_space,
                    'paint_condition' => $this->paint_condition,
                    'coverage' => $this->coverage,
                ],
            ]);
        }

        session()->flash('message', 'Your estimate is ready. We\'ll reach out soon to follow up.');

        // Submit to external API
        $projectData = array_merge($this->getCalculationData(), [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'zipCode' => $this->zipCode,
            'estimated_low' => $this->estimated_low,
            'estimated_high' => $this->estimated_high,
        ]);

        $this->formSubmissionService->submit($projectData);

        $this->dispatch('estimator-completed', eventData: [
            'project_type' => $this->project_type,
            'estimated_low' => $this->estimated_low,
            'estimated_high' => $this->estimated_high,
            'currency' => config('sabhero-estimator.defaults.currency', 'USD'),
        ]);

        // Stay on review step (4 when contact first, 5 when contact last)
        $this->step = $this->contactInfoFirst ? 4 : 5;
    }

    public function updatedProjectType($value)
    {
        if ($value === 'interior' && ! $this->interiorScopeShowChoice) {
            $this->interior_scope = $this->interiorScopeDefault;
        }

        // Rebuild surface types for the new project type
        $this->loadSurfaceTypes();

        // Reset interior-specific selections when switching away from interior
        if ($value !== 'interior') {
            $this->interior_scope = null;
            $this->full_floor_space = null;
            $this->full_items = [];
        }
    }

    public function updatedFullItems()
    {
        if ($this->project_type === 'interior' && $this->interior_scope === 'full') {
            $this->calculateEstimate();
        }
    }

    public function updatedFullFloorSpace()
    {
        if ($this->project_type === 'interior' && $this->interior_scope === 'full') {
            $this->calculateEstimate();
        }
    }

    public function updatedInteriorScope($value)
    {
        // Clear any previous validation errors
        $this->resetValidation();

        if ($value === 'full') {
            // Switching to full: clear partial data
            $this->areas = [
                [
                    'name' => '',
                    'surfaces' => [
                        [
                            'surface_type' => '',
                            'measurement' => null,
                            'quantity' => 1,
                        ],
                    ],
                ],
            ];
        } elseif ($value === 'partial') {
            // Switching to partial: clear full data
            $this->full_floor_space = null;
            $this->full_items = [];
        }

        // Reload surface types with correct filtering
        $this->loadSurfaceTypes();

        // If we're already on measurements step, optionally recalc
        if ($this->step === 3 && $this->project_type === 'interior') {
            $this->calculateEstimate();
        }
    }

    public function render()
    {
        return view('sabhero-estimator::livewire.project-estimator');
    }
}
