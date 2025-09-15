<?php

// config for Fuelviews/SabHeroEstimator
return [
    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control the database table names and connection used
    | by the estimator package.
    |
    */
    'database' => [
        'table_prefix' => env('ESTIMATOR_TABLE_PREFIX', 'estimator_'),
        'connection' => env('DB_CONNECTION', 'mysql'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the routing for the estimator package, including middleware
    | and route prefixes.
    |
    */
    'routes' => [
        'enabled' => true,
        'prefix' => env('ESTIMATOR_ROUTE_PREFIX', 'estimator'),
        'middleware' => ['web'],
        'name' => 'estimator.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Filament Integration
    |--------------------------------------------------------------------------
    |
    | Configure Filament admin panel integration settings.
    |
    */
    'filament' => [
        'enabled' => env('ESTIMATOR_FILAMENT_ENABLED', true),
        'navigation_group' => 'Estimator',
        'navigation_sort' => 100,
        'register_resources' => [
            'settings' => true,
            'multipliers' => true,
            'rates' => true,
            'projects' => true,
        ],
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
        'enabled' => env('ESTIMATOR_FORM_SUBMISSION_ENABLED', false),
        'production_url' => env('ESTIMATOR_PROD_URL', 'https://api.fuelforms.com/estimator'),
        'development_url' => env('ESTIMATOR_DEV_URL', 'https://dev-api.fuelforms.com/estimator'),
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
        'deviation_percentage' => env('ESTIMATOR_DEVIATION_PERCENTAGE', 35),
        'currency' => env('ESTIMATOR_CURRENCY', 'USD'),
        'currency_symbol' => env('ESTIMATOR_CURRENCY_SYMBOL', '$'),
        'decimal_places' => env('ESTIMATOR_DECIMAL_PLACES', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the user interface components and styling.
    |
    */
    'ui' => [
        'theme' => env('ESTIMATOR_THEME', 'default'),
        'show_progress_bar' => env('ESTIMATOR_SHOW_PROGRESS', true),
        'show_debug_info' => env('ESTIMATOR_SHOW_DEBUG', false),
        'steps' => [
            'welcome' => [
                'title' => 'Welcome to Our Painting Estimator',
                'description' => 'Please follow the steps to enter your project details. You will first provide your contact information, then your project measurements, and finally review your estimate.',
            ],
            'contact' => [
                'title' => 'Contact Information',
                'description' => 'Please provide your contact details and project type.',
            ],
            'measurements' => [
                'title' => 'Project Measurements',
                'description' => 'Enter the details about your painting project.',
            ],
            'review' => [
                'title' => 'Your Price Estimate',
                'description' => 'Review your project estimate and submit for follow-up.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for caching estimator data to improve performance.
    |
    */
    'cache' => [
        'enabled' => env('ESTIMATOR_CACHE_ENABLED', true),
        'ttl' => env('ESTIMATOR_CACHE_TTL', 3600), // 1 hour
        'prefix' => 'estimator_',
    ],
];
