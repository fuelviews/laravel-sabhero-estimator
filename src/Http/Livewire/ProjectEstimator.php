<?php

namespace Fuelviews\SabHeroEstimator\Http\Livewire;

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

    public $name;

    public $email;

    public $phone;

    public $address;

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

    // Calculated estimates
    public $estimated_low;

    public $estimated_high;

    // Interior scope (Full vs Partial)
    public ?string $interior_scope = null;

    public ?float $full_floor_space = null;

    public array $full_items = [];

    public array $interiorFullItems = [];

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
        $this->interior_scope = null;
        $this->full_floor_space = null;
        $this->full_items = [];
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
        // Check that the area exists and that there is more than one surface in that area
        if (isset($this->areas[$areaIndex]['surfaces']) && count($this->areas[$areaIndex]['surfaces']) > 1) {
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

        // STEP 4 ← STEP 3
        if ($this->step === 4) {
            $this->step = 3;

            return;
        }

        // STEP 3 ← STEP 2
        if ($this->step === 3) {
            // We're going back to contact info, hide its errors again
            $this->touchedContact = false;
            $this->step = 2;

            return;
        }

        // STEP 2 ← STEP 1
        if ($this->step === 2) {
            $this->step = 1;

            return;
        }
    }

    public function nextStep()
    {
        // Clear any previous validation/errors before moving
        $this->resetValidation();

        // STEP 1 → STEP 2 (no validation)
        if ($this->step === 1) {
            $this->step = 2;

            return;
        }

        // STEP 2 → STEP 3 (validate contact info)
        if ($this->step === 2) {
            $this->touchedContact = true;
            $this->validateStep(2);
            $this->step = 3;

            return;
        }

        // STEP 3 → STEP 4 (validate measurements, then calculate)
        if ($this->step === 3) {
            $this->validateStep(3);
            $this->calculateEstimate();
            $this->step = 4;

            return;
        }
    }

    // Validation for each step
    public function validateStep($step)
    {
        if ($step == 2) {
            $this->validate([
                'project_type' => 'required|in:interior,exterior',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:50',
                'address' => 'required|string|max:255',
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
                $rules = [
                    'house_style' => 'required|string',
                    'number_of_floors' => 'required|integer|min:1',
                    'total_floor_space' => 'required|numeric|min:0',
                    'paint_condition' => 'required|string',
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
                    'paint_condition.required' => 'Please select the condition of the existing paint.',
                    'coverage.required' => 'Please select how much of the house is being painted.',
                ];

                $this->validate($rules, $messages);
            }
        }
    }

    public function calculateEstimate()
    {
        $data = $this->getCalculationData();
        $result = $this->calculationService->calculate($data);

        $this->estimated_low = $result['low'];
        $this->estimated_high = $result['high'];
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'estimated_low' => $this->estimated_low,
            'estimated_high' => $this->estimated_high,
        ]);

        if ($this->project_type === 'interior') {
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

        session()->flash('message', 'Project submitted successfully!');

        // Submit to external API
        $projectData = array_merge($this->getCalculationData(), [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'estimated_low' => $this->estimated_low,
            'estimated_high' => $this->estimated_high,
        ]);

        $this->formSubmissionService->submit($projectData);

        // Update the step to 4 to show the review panel with calculated price
        $this->step = 4;
    }

    public function updatedProjectType($value)
    {
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
