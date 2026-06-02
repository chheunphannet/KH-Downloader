<?php

return [
    /*
    |--------------------------------------------------------------------------
    | yt-dlp binary
    |--------------------------------------------------------------------------
    */
    'yt_dlp_binary' => env('YT_DLP_BINARY', 'yt-dlp'),

    /*
    |--------------------------------------------------------------------------
    | Temp directory for yt-dlp downloads
    |--------------------------------------------------------------------------
    */
    'download_temp_path' => env('DOWNLOAD_TEMP_PATH', storage_path('app/yt-dlp-temp')),

    /*
    |--------------------------------------------------------------------------
    | FlareSolverr — Cloudflare bypass proxy
    |--------------------------------------------------------------------------
    | Set FLARESOLVERR_ENABLED=true in .env to activate.
    | URL should point to the FlareSolverr service (Docker service name or IP).
    | Timeout is in milliseconds.
    */
    'flaresolverr' => [
        'enabled' => env('FLARESOLVERR_ENABLED', false),
        'url'     => env('FLARESOLVERR_URL', 'http://flaresolverr:8191/v1'),
        'timeout' => (int) env('FLARESOLVERR_TIMEOUT_MS', 60000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTLs (in seconds)
    |--------------------------------------------------------------------------
    | postid_ttl : how long to cache the khdiamond post ID.
    |              0 = cache forever (recommended — postid never changes).
    | embed_ttl  : how long to cache the embed/stream URL.
    |              khdiamond embed URLs can expire, so keep this short.
    */
    'cache' => [
        'postid_ttl' => (int) env('CACHE_POSTID_TTL', 0),       // 0 = forever
        'embed_ttl'  => (int) env('CACHE_EMBED_TTL', 21600),     // 6 hours
    ],
];
