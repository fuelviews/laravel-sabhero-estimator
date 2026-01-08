@php use Fuelviews\SabHeroEstimator\Models\Rate; @endphp
<div class="max-w-xl mx-auto mb-12 p-4 lg:p-0">
    <!-- Progress Bar (optional) -->
    <div class="mb-4">
        <div class="flex justify-between">
            <span class="{{ $step >= 1 ? 'font-bold' : '' }}">Welcome</span>
            <span class="{{ $step >= 2 ? 'font-bold' : '' }}">Contact Info</span>
            <span class="{{ $step >= 3 ? 'font-bold' : '' }}">Measurements</span>
            <span class="{{ $step >= 4 ? 'font-bold' : '' }}">Review</span>
        </div>
        <div class="w-full bg-gray-200 h-2 rounded">
            <div class="bg-blue-500 h-2 rounded" style="width: {{ ($step / 4) * 100 }}%"></div>
        </div>
    </div>

    <!-- Step 1: Cover Page -->
    @if ($step === 1)
        <div class="p-8 text-center">
            <h1 class="text-3xl font-bold mb-4">Welcome to Our Painting Estimator</h1>
            <p class="mb-8">
                Please follow the steps to enter your project details. You will first provide your contact information, then your project measurements, and finally review your estimate.
            </p>
            <button wire:click="nextStep" class="bg-blue-500 text-white px-6 py-2 rounded-standard">
                Continue
            </button>
        </div>
    @endif

   <!-- Step 2: Contact Info -->
@if ($step === 2)
    <div>
        <!-- Project Type -->
        <div class="mt-4">
            <label for="project_type" class="block font-medium">Project Type</label>
            <select
                id="project_type"
                wire:model.live="project_type"
                class="mt-1 block w-full rounded-standard border p-2 @error('project_type') border-red-500 @else border-gray-300 @enderror"
            >
                <option value="">Choose...</option>
                <option value="interior">Interior</option>
                <option value="exterior">Exterior</option>
            </select>
            @error('project_type')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Name -->
        <div class="mt-4">
            <label class="block font-medium">Name</label>
            <div class="mt-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <input
                        id="first_name"
                        type="text"
                        autocomplete="given-name"
                        placeholder="First name"
                        wire:model.live="first_name"
                        class="block w-full rounded-standard border p-2 @error('first_name') border-red-500 @else border-gray-300 @enderror"
                    />
                    @error('first_name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <input
                        id="last_name"
                        type="text"
                        autocomplete="family-name"
                        placeholder="Last name"
                        wire:model.live="last_name"
                        class="block w-full rounded-standard border p-2 @error('last_name') border-red-500 @else border-gray-300 @enderror"
                    />
                    @error('last_name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Email -->
        <div class="mt-4">
            <label for="email" class="block font-medium">Email</label>
            <input
                id="email"
                type="email"
                wire:model.live="email"
                class="mt-1 block w-full rounded-standard border p-2 @error('email') border-red-500 @else border-gray-300 @enderror"
            />
            @error('email')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Phone -->
        <div class="mt-4">
            <label for="phone" class="block font-medium">Phone</label>
            <input
                id="phone"
                type="text"
                wire:model.live="phone"
                class="mt-1 block w-full rounded-standard border p-2 @error('phone') border-red-500 @else border-gray-300 @enderror"
            />
            @error('phone')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Zip Code -->
        <div class="mt-4">
            <label for="zipCode" class="block font-medium">Zip Code</label>
            <input
                id="zipCode"
                type="text"
                wire:model.live="zipCode"
                class="mt-1 block w-full rounded-standard border p-2 @error('zipCode') border-red-500 @else border-gray-300 @enderror"
            />
            @error('zipCode')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-6">
            <button
                wire:click="previousStep"
                class="bg-gray-500 text-white px-4 py-2 rounded-standard"
            >
                Back
            </button>
            <button
                wire:click="nextStep"
                class="bg-blue-500 text-white px-4 py-2 rounded-standard"
            >
                Next
            </button>
        </div>
    </div>
@endif

    <!-- Step 3: Measurements -->
    @if ($step === 3)
        @if ($project_type === 'interior')
            {{-- Choose interior scope (always shown so user can switch) --}}
            <div class="p-4 border rounded-standard mb-4">
                <h3 class="text-lg font-bold mb-4">What part of the interior are we painting?</h3>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model.live="interior_scope" value="full" class="text-blue-600">
                        <span>Full Interior <small class="text-gray-500">(entire house)</small></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model.live="interior_scope" value="partial" class="text-blue-600">
                        <span>Partial Interior <small class="text-gray-500">(specific rooms)</small></span>
                    </label>
                </div>
                @error('interior_scope')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
            @if ($interior_scope === 'partial')
            <div>
                @foreach ($areas as $index => $area)
                    <div class="border p-4 rounded-standard mb-4">
                        <label>Area Name (optional)</label>
                        <input type="text" wire:model.live="areas.{{ $index }}.name" class="mt-1 rounded-standard block w-full" />

                        @foreach ($area['surfaces'] as $sindex => $surface)
                        <div class="mt-2 p-2 rounded-standard border relative">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-medium text-sm text-gray-700">Surface {{ $sindex + 1 }}</h4>
                                @if (count($area['surfaces']) > 1)
                                    <button 
                                        type="button"
                                        wire:click.prevent="removeSurface({{ $index }}, {{ $sindex }})"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium px-2 py-1 rounded hover:bg-red-50 transition-colors"
                                        title="Remove this surface"
                                    >
                                        × Remove
                                    </button>
                                @endif
                            </div>
                            <label>Surface Type</label>
                            <select wire:model.live="areas.{{ $index }}.surfaces.{{ $sindex }}.surface_type" class="mt-1 block w-full rounded-standard">
                                <option value="">Select Surface Type</option>
                                @foreach ($surfaceTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                             @error('areas.'.$index.'.surfaces.'.$sindex.'.surface_type')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror

                            @php
                                // Get the selected surface type (as a string) from the current surface array
                                $selectedType = $area['surfaces'][$sindex]['surface_type'] ?? null;
                                // Determine the input type by querying the Rate model for the given surface type.
                                // (In production, consider caching or pre-loading these values.)
                                $inputType = null;
                                if ($selectedType) {
                                    $rateRecord = Rate::where('surface_type', $selectedType)->first();
                                    $inputType = $rateRecord ? $rateRecord->input_type : null;
                                }
                            @endphp

                            @if ($inputType === 'measurement')
                                <label>Square Footage</label>
                                <input type="number" step="0.01" wire:model.live="areas.{{ $index }}.surfaces.{{ $sindex }}.measurement" class="mt-1 block w-full rounded-standard" placeholder="Enter square footage" />
                                @error('areas.'.$index.'.surfaces.'.$sindex.'.measurement')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            @elseif ($inputType === 'quantity')
                                <label>Quantity</label>
                                <input type="number" wire:model.live="areas.{{ $index }}.surfaces.{{ $sindex }}.quantity" class="mt-1 block w-full rounded-standard" placeholder="Enter quantity" />
                                @error('areas.'.$index.'.surfaces.'.$sindex.'.quantity')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            @else
                                <small>Please select a surface type.</small>
                            @endif
                        </div>
                    @endforeach

                        <button wire:click.prevent="addSurface({{ $index }})" class="mt-2 text-blue-500">+ Add Surface</button>
                    </div>
                @endforeach
                <button wire:click.prevent="addArea" class="text-blue-500">+ Add Area</button>
            </div>
            @endif
            @if ($interior_scope === 'full')
                <div class="border p-4 rounded-standard mb-4">
                    <label for="full_floor_space" class="block font-medium">Total Floor Space (sq&nbsp;ft)</label>
                    <input
                        id="full_floor_space"
                        type="number"
                        step="0.01"
                        wire:model.live.debounce.500ms="full_floor_space"
                        wire:change="calculateEstimate"
                        class="mt-1 block w-full rounded-standard border p-2 @error('full_floor_space') border-red-500 @else border-gray-300 @enderror"
                        placeholder="e.g. 2400"
                    />
                    @error('full_floor_space')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    <fieldset class="mt-4">
                        <legend class="font-medium">Items to paint</legend>
                        <div class="flex flex-wrap gap-4 mt-2">
                            @foreach ($interiorFullItems as $itemKey => $itemLabel)
                                <label class="inline-flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        wire:model.live="full_items"
                                        wire:change="calculateEstimate"
                                        value="{{ $itemKey }}"
                                        class="text-blue-600"
                                    >
                                    <span>{{ $itemLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('full_items')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </fieldset>
                </div>
            @endif
        @else
        <!-- Exterior fields -->
<div class="border p-4 rounded-standard">
    <label class="block font-medium">{{ $selectHouseStyleLabel }}</label>
    <div class="flex flex-wrap gap-4 mt-2 justify-center mx-auto">
        @forelse ($houseStyles as $style)
            <label class="cursor-pointer w-1/5">
                <input type="radio" wire:model.live="house_style" value="{{ $style['key'] }}" class="hidden">
                <div class="border-2 rounded h-40 flex flex-col items-center justify-between {{ $house_style === $style['key'] ? 'border-blue-500' : 'border-gray-300' }} p-0">
                    <div class="w-full h-28 overflow-hidden">
                        @if(!empty($style['image']))
                            @php
                                $disk = config('sabhero-estimator.media.disk');
                                $imagePath = $style['image'];
                                $imageUrl = \Illuminate\Support\Facades\Storage::disk($disk)->url($imagePath);
                            @endphp
                            <img src="{{ $imageUrl }}"
                                 alt="{{ $style['key'] }}"
                                 class="w-full h-28 object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                <!-- Heroicons Outline Home Icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-500"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 22V12h6v10" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="w-full text-center text-sm font-medium p-1">
                        {{ ucfirst($style['key']) }}
                    </div>
                </div>
            </label>
        @empty
            <p>No house styles available.</p>
        @endforelse
        @error('house_style')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

    <div>
        <label for="coverage" class="block font-medium mt-2">
            {{ $coverageLabel }}
        </label>
        <select id="coverage" wire:model.live="coverage" class="mt-2 block w-full border border-gray-300 rounded-standard p-2">
            @foreach ($coverageOptions as $option)
                <option value="{{ $option['key'] }}">
                    {{ $option['key'] }}
                </option>
            @endforeach
        </select>
        @error('coverage')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="number_of_floors" class="block font-medium mt-2">
            {{ $numberOfFloorsLabel }}
        </label>
        <select id="number_of_floors" wire:model.live="number_of_floors" class="mt-1 block w-full border border-gray-300 rounded-standard p-2">
            <option value="">{{ $numberOfFloorsDefaultOption }}</option>
            @foreach ($floorOptions as $floor)
                <option value="{{ $floor }}">{{ $floor }}</option>
            @endforeach
        </select>
        @error('number_of_floors')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

            <label for="total_floor_space" class="mt-2 block">Total Floor Space (sq ft)</label>
            <input type="number" step="0.01" id="total_floor_space" wire:model.live="total_floor_space" class="mt-1 block border border-gray-300 w-full rounded-standard" placeholder="Enter total floor space">
            @error('total_floor_space')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror

            @php
    // Only show paint_condition if there are real options
    $hasPaintConditionOptions = !empty($paintConditionOptions) && collect($paintConditionOptions)
        ->filter(fn($o) => !empty($o['key']))
        ->count() > 0;
@endphp

@if ($hasPaintConditionOptions)
    <label for="paint_condition" class="mt-2 block font-medium">
        {{ $paintConditionLabel }}
    </label>
    <select id="paint_condition"
            wire:model.live="paint_condition"
            class="mt-1 block w-full border border-gray-300 rounded-standard p-2">
        <option value="">{{ $paintConditionDefaultOption }}</option>
        @foreach ($paintConditionOptions as $option)
            @if (!empty($option['key']))
                <option value="{{ $option['key'] }}">{{ ucfirst($option['key']) }}</option>
            @endif
        @endforeach
    </select>
    @error('paint_condition')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
@endif

            <div class="flex justify-between mt-4">
                <button wire:click="previousStep" class="bg-gray-500 text-white px-4 py-2 rounded-standard">Back</button>
                <div x-data="{ showConfirm: false }">
                    <!-- Calculate Estimate Button -->
                    <button @click="showConfirm = true" class="bg-blue-500 text-white px-4 py-2 rounded-standard">
                        Calculate Estimate
                    </button>

                    <!-- Confirmation Modal -->
                    <div x-show="showConfirm" x-transition class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                        <div class="bg-white p-6 rounded-standard shadow-lg max-w-md w-full">
                            <h3 class="text-xl font-bold mb-4">Confirm Submission</h3>
                            <p class="mb-4">
                                By clicking "Calculate Estimate," your information will be sent to us and we will contact you via phone or email to follow up.
                            </p>
                            <div class="flex justify-end">
                                <button @click="showConfirm = false" class="mr-2 px-4 py-2 border rounded-standard">
                                    Cancel
                                </button>
                                <!-- When confirmed, call the Livewire method to submit and then hide the modal -->
                                <button @click="$wire.submitProject(); showConfirm = false" class="bg-blue-500 text-white px-4 py-2 rounded-standard">
                                    Yes, Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @endif

        <div class="flex justify-between mt-4">
            <button wire:click="previousStep" class="bg-gray-500 text-white px-4 py-2 rounded-standard">Back</button>

            @if ($project_type === 'interior')
                <!-- Interior projects show confirmation modal just like exterior -->
                <div x-data="{ showConfirm: false }">
                    <button @click="showConfirm = true" class="bg-blue-500 text-white px-4 py-2 rounded-standard">
                        Calculate Estimate
                    </button>

                    <!-- Confirmation Modal -->
                    <div x-show="showConfirm" x-transition class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                        <div class="bg-white p-6 rounded-standard shadow-lg max-w-md w-full">
                            <h3 class="text-xl font-bold mb-4">Confirm Submission</h3>
                            <p class="mb-4">
                                By clicking "Calculate Estimate," your information will be sent to us and we will contact you via phone or email to follow up.
                            </p>
                            <div class="flex justify-end">
                                <button @click="showConfirm = false" class="mr-2 px-4 py-2 border rounded-standard">
                                    Cancel
                                </button>
                                <button @click="$wire.submitProject(); showConfirm = false" class="bg-blue-500 text-white px-4 py-2 rounded-standard">
                                    Yes, Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Exterior projects show confirmation modal -->
                <div x-data="{ showConfirm: false }">
                    <button @click="showConfirm = true" class="bg-blue-500 text-white px-4 py-2 rounded-standard">
                        Calculate Estimate
                    </button>

                    <!-- Confirmation Modal -->
                    <div x-show="showConfirm" x-transition class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                        <div class="bg-white p-6 rounded-standard shadow-lg max-w-md w-full">
                            <h3 class="text-xl font-bold mb-4">Confirm Submission</h3>
                            <p class="mb-4">
                                By clicking "Calculate Estimate," your information will be sent to us and we will contact you via phone or email to follow up.
                            </p>
                            <div class="flex justify-end">
                                <button @click="showConfirm = false" class="mr-2 px-4 py-2 border rounded-standard">
                                    Cancel
                                </button>
                                <button @click="$wire.submitProject(); showConfirm = false" class="bg-blue-500 text-white px-4 py-2 rounded-standard">
                                    Yes, Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Step 4: Review and Submit -->
    @if ($step === 4)
    <div class="p-4 border">
        <h2 class="text-xl mb-2">Your Price Estimate</h2>
        <p>Estimated Range: <strong>${{ number_format($estimated_low, 2) }}</strong> - <strong>${{ number_format($estimated_high, 2) }}</strong></p>
        <!-- Optionally, display a breakdown or further instructions -->
    </div>
    <div class="flex justify-between mt-4">
        <button wire:click="previousStep" class="bg-gray-500 text-white px-4 py-2 rounded-standard">Back</button>
        <!-- Optionally, you can have a final "Confirm" button here if needed -->
    </div>
@endif

</div>
