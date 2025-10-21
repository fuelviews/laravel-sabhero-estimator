<?php

// config for Fuelviews/SabHeroEstimator
return [
    /*
    |--------------------------------------------------------------------------
    | Table Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control the database table names used by the estimator package.
    |
    */
    'table' => [
        'prefix' => 'estimator_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Form Submission Endpoints
    |--------------------------------------------------------------------------
    |
    | Configure external API endpoints for form submissions. The package
    | will send estimation data to FuelForms for processing.
    |
    */
    'form_endpoints' => [
        'production_url' => config('forms.forms.free_estimate.production_url', ''),
        'development_url' => config('forms.forms.free_estimate.development_url', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Calculation Defaults
    |--------------------------------------------------------------------------
    |
    | Default values used in price calculations and estimations.
    |
    */
    'defaults' => [
        'currency_symbol' => '$',
        'decimal_places' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the filesystem disk for storing house style images.
    | You can use any configured Laravel filesystem disk (public, s3, etc.).
    |
    */
    'media' => [
        'disk' => 'public',
    ],
];
