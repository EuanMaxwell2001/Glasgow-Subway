<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SPT API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the SPT Disruptions API integration
    |
    */

    'api_base' => env('SPT_API_BASE', 'https://www.spt.co.uk/api/disruption/category/'),
    'source' => env('SPT_SOURCE', 'live'), // live or fixture
    'timeout' => env('SPT_API_TIMEOUT', 10),
    'staleness_threshold' => env('STALENESS_THRESHOLD', 10), // minutes
];
