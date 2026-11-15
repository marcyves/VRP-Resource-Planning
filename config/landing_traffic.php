<?php

return [
    /*
    | First-party landing-page visit tracking (super-admin only).
    | No third-party analytics. Country is filled only from a local GeoLite2
    | MMDB (same path as login stats). Raw IP is hashed and never shown.
    */
    'enabled' => filter_var(env('LANDING_TRAFFIC_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    'display_timezone' => env('LANDING_TRAFFIC_TIMEZONE', 'Europe/Rome'),

    'cookie' => 'vrp_lv',

    'cookie_minutes' => 60 * 24 * 365,

    'respect_dnt' => true,
];
