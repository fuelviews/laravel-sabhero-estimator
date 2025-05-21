<?php

namespace Fuelviews\SabHeroEstimator\Livewire;

use Livewire\Component;
use Fuelviews\SabHeroEstimator\Models\Project;
use Fuelviews\SabHeroEstimator\Models\Area;
use Fuelviews\SabHeroEstimator\Models\Surface;
use Fuelviews\SabHeroEstimator\Models\Rate;
use Fuelviews\SabHeroEstimator\Models\Multiplier;
use Fuelviews\SabHeroEstimator\Models\Setting;
//use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
// use Fuelviews\SabHeroEstimator\Mail\CustomerEstimate;
// use Fuelviews\SabHeroEstimator\Mail\AdminProjectSubmission;
use Illuminate\Support\Facades\Log;

class ProjectEstimator extends Component
{

    // set the form endpoint
    public $formEndpoint;



    public $test = '';

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

    // Calculated estimates
    public $estimated_low;
    public $estimated_high;


    // Define which field should be displayed for each surface type.
    // 'measurement' means show a square footage input,
    // 'quantity' means show a number input (for count).
    public $surfaceInputMapping = [
        'interior_wall' => 'measurement',
        'door'          => 'quantity',
        'window'        => 'quantity',
        // add additional mappings as needed.
];

    public function mount()
    {
        // Load condition options from the multipliers table (category 'condition')
        $this->paintConditionOptions = \Fuelviews\SabHeroEstimator\Models\Multiplier::where('category', 'condition')
            ->orderBy('key', 'asc') // Order as desired
            ->get(['key', 'value'])
            ->toArray();

        // Load condition label and default option from settings
        $this->paintConditionLabel = \Fuelviews\SabHeroEstimator\Models\Setting::where('key', 'paint_condition_label')
            ->value('value') ?? 'Condition of Existing Paint';

        $this->paintConditionDefaultOption = \Fuelviews\SabHeroEstimator\Models\Setting::where('key', 'paint_condition_default_option')
            ->value('value') ?? 'Select condition';

        // Load the coverage label setting.
    $this->coverageLabel = \Fuelviews\SabHeroEstimator\Models\Setting::where('key', 'coverage_label')
    ->value('value') ?? 'How much of the house is being painted?';

    // Load distinct surface types from the rates table.
        // Here we use pluck() with the same field for key and value.
        $surfaceTypes = \Fuelviews\SabHeroEstimator\Models\Rate::where('project_type', $this->project_type) // e.g., 'interior'
    ->distinct()
    ->pluck('surface_type', 'surface_type')
    ->toArray();

        // Optionally, set a default selection if needed.
        if (empty($this->selectedSurfaceType) && !empty($this->surfaceTypes)) {
            $this->selectedSurfaceType = array_key_first($this->surfaceTypes);
        }





        // Load the endpoint using the 'instant' key from forms.php.
    $this->formEndpoint = app()->environment('production')
    ? config('forms.instant.production_url')
    : config('forms.instant.development_url');

        // Initialize with one default area and one default surface
        $this->areas = [
            ['name' => '', 'surfaces' => [
                ['surface_type' => '', 'measurement' => null, 'quantity' => 1],
            ]],
        ];

        // Retrieve house style records with their key and image
        $this->houseStyles = \Fuelviews\SabHeroEstimator\Models\Multiplier::where('category', 'house_style')
        ->distinct()
        ->get(['key', 'image'])
        ->toArray();

        // Load the "Select House Style" label from the settings table.
        $this->selectHouseStyleLabel = \Fuelviews\SabHeroEstimator\Models\Setting::where('key', 'select_house_style_label')
            ->value('value') ?? 'Select House Style:';

        // Load available floor options from the multipliers table where category is "floor"
        $this->floorOptions = \Fuelviews\SabHeroEstimator\Models\Multiplier::where('category', 'floor')
            ->orderBy('key', 'asc') // assuming the key is stored as a numeric string, e.g. "1", "2", etc.
            ->pluck('key')
            ->toArray();

        // Optionally, set a default value if none is provided
        if (empty($this->number_of_floors) && !empty($this->floorOptions)) {
            $this->number_of_floors = $this->floorOptions[0];
        }

        // Load settings for the floors label and default option
        $this->numberOfFloorsLabel = \Fuelviews\SabHeroEstimator\Models\Setting::where('key', 'number_of_floors_label')
            ->value('value') ?? 'Number of Floors';

        $this->numberOfFloorsDefaultOption = \Fuelviews\SabHeroEstimator\Models\Setting::where('key', 'number_of_floors_default_option')
            ->value('value') ?? 'Select the number of floors';

        // Load coverage options as an array from the multipliers table.
        $this->coverageOptions = \Fuelviews\SabHeroEstimator\Models\Multiplier::where('category', 'coverage')
        ->orderBy('value', 'desc')
        ->get(['key', 'value'])
        ->toArray();
// Set default value if not already set.
if (!$this->coverage) {
    $this->coverage = 'The Entire House';
}

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

    // Navigation methods
    public function previousStep()
    {
        $this->step = max(1, $this->step - 1);
    }

    public function nextStep()
    {
        $this->validateStep($this->step);
        $this->step++;
    }

    // Validation for each step
    public function validateStep($step)
    {
        if ($step == 2) {
            $this->validate([
                'project_type' => 'required|in:interior,exterior',
                'name'         => 'required|string|max:255',
                'email'        => 'required|email|max:255',
                'phone'        => 'required|string|max:50',
                'address'      => 'required|string|max:255',
            ]);
        }

        if ($step == 3) {
        if ($this->project_type === 'interior') {
    $rules    = [];
    $messages = [];

    foreach ($this->areas as $i => $area) {
        foreach ($area['surfaces'] as $j => $surface) {
            $fieldTypeKey = "areas.$i.surfaces.$j.surface_type";
            $rules[$fieldTypeKey] = 'required|in:'.implode(',', array_keys($this->surfaceTypes));
            $messages["{$fieldTypeKey}.required"] = 'At least one surface is required.';

            // **Fetch the input_type from the DB**
            $inputType = \Fuelviews\SabHeroEstimator\Models\Rate::where('surface_type', $surface['surface_type'])
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

    $this->validate($rules, $messages);

        } else {
            // Exterior
            $rules = [
                'house_style'       => 'required|string',
                'number_of_floors'  => 'required|integer|min:1',
                'total_floor_space' => 'required|numeric|min:0',
                'paint_condition'   => 'required|string',
                'coverage'          => 'required|string',
            ];
            $messages = [
                'house_style.required'       => 'Please choose a house style.',
                'number_of_floors.required'  => 'Please select the number of floors.',
                'number_of_floors.integer'   => 'Number of floors must be a whole number.',
                'number_of_floors.min'       => 'Number of floors must be at least 1.',
                'total_floor_space.required' => 'Please enter the total floor space.',
                'total_floor_space.numeric'  => 'Floor space must be a number.',
                'total_floor_space.min'      => 'Floor space must be at least zero.',
                'paint_condition.required'   => 'Please select the condition of the existing paint.',
                'coverage.required'          => 'Please select how much of the house is being painted.',
            ];

            $this->validate($rules, $messages);
        }
    }
    }

    public $debugBreakdown = [];

    public function calculateEstimate()
    {
        // Retrieve the deviation percentage (stored as a percentage, e.g., "15" for 15%)
        $deviation = \Fuelviews\SabHeroEstimator\Models\Setting::getDeviationPercentage();

        $totalCost = 0;
        $this->debugBreakdown = []; // Reset any previous debug data

        if ($this->project_type === 'interior') {
            foreach ($this->areas as $areaIndex => $area) {
                foreach ($area['surfaces'] as $surfaceIndex => $surface) {
                    // Get the rate for the given surface type
                    $rateModel = \Fuelviews\SabHeroEstimator\Models\Rate::where('surface_type', $surface['surface_type'])->first();
                    if ($rateModel) {
                        // For walls, use measurement; for doors/windows, use quantity * rate
                        $cost = in_array($surface['surface_type'], ['door', 'window'])
                            ? $surface['quantity'] * $rateModel->rate
                            : $surface['measurement'] * $rateModel->rate;
                        $totalCost += $cost;

                        // Record debug information for this surface
                        $this->debugBreakdown[] = [
                            'area'         => $areaIndex,
                            'surface'      => $surfaceIndex,
                            'surface_type' => $surface['surface_type'],
                            'measurement'  => $surface['measurement'] ?? 0,
                            'quantity'     => $surface['quantity'] ?? 0,
                            'rate'         => $rateModel->rate,
                            'cost'         => $cost,
                        ];
                    }
                }
            }
        } else {
            // For exterior projects:
            // Retrieve the base rate for exterior projects.
            $rateModel = \Fuelviews\SabHeroEstimator\Models\Rate::where('surface_type', 'exterior')->first();
            if ($rateModel) {
                $baseCost = $this->total_floor_space * $rateModel->rate;
            } else {
                $baseCost = 0;
            }

            // Define the individual multipliers for additive combination.
            // These values can be adjusted or moved to configuration.
            $houseStyleMultiplier = (float) \Fuelviews\SabHeroEstimator\Models\Multiplier::where('category', 'house_style')
            ->where('key', $this->house_style)
            ->value('value') ?? 1;
            // Retrieve the floor multiplier from the database for the selected number of floors.
            $floorMultiplier = (float) \Fuelviews\SabHeroEstimator\Models\Multiplier::where('category', 'floor')
            ->where('key', (string)$this->number_of_floors)
            ->value('value') ?? 1;
            $paintConditionMultiplier = (float) \Fuelviews\SabHeroEstimator\Models\Multiplier::where('category', 'condition')
            ->where('key', $this->paint_condition)
            ->value('value') ?? 1;

            $houseStyleMultiplier = (float) \Fuelviews\SabHeroEstimator\Models\Multiplier::where('category', 'house_style')
            ->where('key', $this->house_style)
            ->value('value') ?? 1;
            $floorMultiplier = (float) \Fuelviews\SabHeroEstimator\Models\Multiplier::where('category', 'floor')
            ->where('key', (string)$this->number_of_floors)
            ->value('value') ?? 1;
            $conditionMultiplier = (float) \Fuelviews\SabHeroEstimator\Models\Multiplier::where('category', 'condition')
            ->where('key', $this->paint_condition)
            ->value('value') ?? 1;

            // Calculate the final additive multiplier for these factors.
            $finalMultiplier = 1 + (
                ($houseStyleMultiplier - 1) +
                ($floorMultiplier - 1) +
                ($conditionMultiplier - 1)
            );

            // Retrieve the coverage multiplier from the database.
            // Make sure $this->coverage is set (default should be '100')
            $coverageMultiplier = (float) \Fuelviews\SabHeroEstimator\Models\Multiplier::where('category', 'coverage')
                ->where('key', $this->coverage)
                ->value('value') ?? 1;

            // Apply both the additive multiplier and the coverage multiplier.
            $totalCost = $baseCost * $finalMultiplier * $coverageMultiplier;
        }

        // Apply the deviation percentage to compute the final estimated range.
        $this->estimated_low = $totalCost * (1 - $deviation);
        $this->estimated_high = $totalCost * (1 + $deviation);
    }

    // Final submission: calculate, save, and (temporarily) disable notifications
    public function submitProject()
    {
        $this->validateStep($this->step);
        $this->calculateEstimate();

        // Save the project data
        $project = Project::create([
            'project_type'   => $this->project_type,
            'name'           => $this->name,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'address'        => $this->address,
            'estimated_low'  => $this->estimated_low,
            'estimated_high' => $this->estimated_high,
        ]);

        if ($this->project_type === 'interior') {
            foreach ($this->areas as $areaData) {
                $area = Area::create([
                    'project_id' => $project->id,
                    'name'       => $areaData['name'] ?? null,
                ]);
                foreach ($areaData['surfaces'] as $surfaceData) {
                    Surface::create([
                        'area_id'      => $area->id,
                        'surface_type' => $surfaceData['surface_type'],
                        'measurement'  => $surfaceData['measurement'],
                        'quantity'     => $surfaceData['quantity'],
                    ]);
                }
            }
        } else {
            $project->update([
                'exterior_details' => json_encode([
                    'house_style'      => $this->house_style,
                    'number_of_floors' => $this->number_of_floors,
                    'total_floor_space'=> $this->total_floor_space,
                    'paint_condition'  => $this->paint_condition,
                ]),
            ]);
        }

        session()->flash('message', 'Project submitted successfully!');

        // Flatten the areas array into a string
        $flatAreasString = '';
        if (!empty($this->areas) && is_array($this->areas)) {
            $flatAreas = array_map(function ($area) {
                $areaName = $area['name'] ?? '';
                $flatSurfaces = array_map(function ($surface) {
                    return $surface['surface_type'] . '|' . $surface['measurement'] . '|' . $surface['quantity'];
                }, $area['surfaces'] ?? []);
                return $areaName . ':' . implode(',', $flatSurfaces);
            }, $this->areas);
            $flatAreasString = implode(';', $flatAreas);
        }

        // Build a JSON payload without arrays
        $payload = json_encode([
            'project_type'   => $this->project_type,
            'name'           => $this->name,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'address'        => $this->address,
            'estimated_low'  => $this->estimated_low,
            'estimated_high' => $this->estimated_high,
            'areas'          => $flatAreasString,
        ]);

        if ($this->formEndpoint) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ' .
                                      'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.102 Safari/537.36',
                ])
                ->withBody($payload, 'application/json')
                ->post($this->formEndpoint);

                if ($response->successful()) {
                    Log::info('Form submission successful. Response:', $response->json() ?? []);
                } else {
                    Log::error('Form submission failed. Response:', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Exception during form submission:', ['message' => $e->getMessage()]);
            }
        } else {
            Log::warning('Form endpoint not set.');
        }

        // Update the step to 4 to show the review panel with calculated price
        $this->step = 4;
    }

    public function updatedProjectType($value)
    {
        $this->surfaceTypes = \Fuelviews\SabHeroEstimator\Models\Rate::where('project_type', $value)
            ->distinct()
            ->pluck('surface_type', 'surface_type')
            ->toArray();
    }

    public function render()
    {

        return view('sabhero-estimator::livewire.project-estimator', [
        ]);
    }
}
