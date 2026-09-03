<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Proximity Driver Matching Radii (in Kilometers)
    |--------------------------------------------------------------------------
    | Expanding search radius steps when attempting to find nearby available drivers.
    */
    'matching_radii' => [3, 5, 10, 20],

    /*
    |--------------------------------------------------------------------------
    | GPS Location Freshness Threshold (in Seconds)
    |--------------------------------------------------------------------------
    | Max seconds allowed since last driver GPS ping before driver is treated as stale.
    */
    'gps_freshness_seconds' => 300, // 5 minutes

    /*
    |--------------------------------------------------------------------------
    | Driver Request Assignment Timeout (in Seconds)
    |--------------------------------------------------------------------------
    | Seconds allowed for a driver to accept/reject an offered ride request.
    */
    'assignment_timeout_seconds' => 45,

    /*
    |--------------------------------------------------------------------------
    | Geofenced Arrival Radius (in Meters)
    |--------------------------------------------------------------------------
    | Distance threshold from driver GPS to pickup location to auto-trigger 'arrived'.
    */
    'arrival_geofence_meters' => 100,

    /*
    |--------------------------------------------------------------------------
    | Dynamic Pricing Engine Constants
    |--------------------------------------------------------------------------
    */
    'base_fare' => 5.00,
    'per_km_rate' => 1.50,
    'per_minute_rate' => 0.25,
    'minimum_fare' => 10.00,
    'additional_stop_fee' => 3.50,
    'max_additional_stops' => 5,
];
