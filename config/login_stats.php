<?php

return [
    /*
    | Geolocation for login statistics.
    |
    | Prefer a local MaxMind GeoLite2 City database (no API key). The MMDB
    | file is too large for the git repository: download it from MaxMind and
    | place it at storage/app/geoip/GeoLite2-City.mmdb (see documentation).
    | When the file is missing, a no-key HTTPS lookup is used with a short
    | timeout. Failures never block login; the event is stored with unknown geo.
    */
    'geo' => [
        'mmdb_path' => env('LOGIN_STATS_GEO_MMDB', storage_path('app/geoip/GeoLite2-City.mmdb')),
        'http_enabled' => filter_var(env('LOGIN_STATS_GEO_HTTP', true), FILTER_VALIDATE_BOOLEAN),
        'http_url' => env('LOGIN_STATS_GEO_HTTP_URL', 'https://ipwho.is/{ip}'),
        'http_timeout' => (float) env('LOGIN_STATS_GEO_HTTP_TIMEOUT', 1.5),
        'cache_ttl' => (int) env('LOGIN_STATS_GEO_CACHE_TTL', 60 * 60 * 24 * 30),
        'failure_cache_ttl' => (int) env('LOGIN_STATS_GEO_FAILURE_CACHE_TTL', 60 * 60),
    ],
];
