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
    | Cloudflare Worker Proxy (recommended over FlareSolverr)
    |--------------------------------------------------------------------------
    | A tiny Cloudflare Worker that fetches pages on behalf of your server.
    | Because it runs inside Cloudflare's own network, it bypasses Cloudflare
    | bot protection (BotFight Mode / Turnstile) on any CF-protected site.
    |
    | CF_WORKER_URL  : Your deployed worker URL
    |                  e.g. https://kh-proxy.yourname.workers.dev
    | CF_WORKER_TOKEN: A secret token you set in the Worker env vars.
    |                  Prevents others from using your worker as a free proxy.
    */
    'cf_worker' => [
        'enabled' => env('CF_WORKER_ENABLED', false),
        'url'     => env('CF_WORKER_URL', ''),
        'token'   => env('CF_WORKER_TOKEN', ''),
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
