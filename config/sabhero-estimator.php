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
        'production_url' => 'https://api.fuelforms.com/estimator',
        'development_url' => 'https://dev-api.fuelforms.com/estimator',
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
];
