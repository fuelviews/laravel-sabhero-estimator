<?php

// config for Fuelviews/SabHeroEstimator
return [
    /*
    |--------------------------------------------------------------------------
    | Media Library Disk
    |--------------------------------------------------------------------------
    |
    | This option controls the disk that will be used for storing media files.
    | The default is the 'public' disk, but you can change it to any disk
    | configured in your filesystem config.
    |
    */
    'media_disk' => env('SABHERO_ESTIMATOR_MEDIA_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Media Library Collection Names
    |--------------------------------------------------------------------------
    |
    | These are the collection names used throughout the package for storing
    | various media types.
    |
    */
    'collections' => [
        'estimator_house_style_image' => 'estimator_house_style_image',
    ],
];
