<?php

use Fuelviews\SabHeroEstimator\Http\Livewire\ProjectEstimator;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sab Hero Estimator Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Sab Hero Estimator package. These routes
| are loaded by the service provider and provide the main estimator
| functionality.
|
*/

$prefix = config('sabhero-estimator.routes.prefix', 'estimator');
$middleware = config('sabhero-estimator.routes.middleware', ['web']);

Route::middleware($middleware)
    ->prefix($prefix)
    ->name(config('sabhero-estimator.routes.name', 'estimator.'))
    ->group(function () {
        // Main estimator route
        Route::get('/', ProjectEstimator::class)->name('index');
    });