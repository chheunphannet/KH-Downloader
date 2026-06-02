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
    | Cloudflare Worker Proxy  ← RECOMMENDED
    |--------------------------------------------------------------------------
    | A tiny Cloudflare Worker that fetches pages on behalf of your server.
    | Because it runs INSIDE Cloudflare's own network, it is trusted by
    | Cloudflare bot protection (BotFight Mode / Turnstile) on any CF-protected
    | site — bypassing the 403 block that your VPS datacenter IP receives.
    |
    | Deploy: see cloudflare-worker/kh-proxy.js
    |
    | CF_WORKER_URL  : Your deployed worker URL
    |                  e.g. https://kh-proxy.yourname.workers.dev
    | CF_WORKER_TOKEN: A secret token set in the Worker environment variables.
    |                  Prevents others from using your worker as a free proxy.
    */
    'cf_worker' => [
        'enabled' => env('CF_WORKER_ENABLED', false),
        'url'     => env('CF_WORKER_URL', ''),
        'token'   => env('CF_WORKER_TOKEN', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | FlareSolverr  ← LEGACY (does NOT work on Cloudflare Turnstile/BotFight)
    |--------------------------------------------------------------------------
    | Only works on older Cloudflare JS challenges.
    | If khdiamond.net is using BotFight Mode, use CF Worker above instead.
    | CF Worker and FlareSolverr are mutually exclusive — CF Worker takes
    | priority if both are enabled.
    */
    'flaresolverr' => [
        'enabled' => env('FLARESOLVERR_ENABLED', false),
        'url'     => env('FLARESOLVERR_URL', 'http://flaresolverr:8191/v1'),
        'timeout' => (int) env('FLARESOLVERR_TIMEOUT_MS', 60000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL for khdiamond post IDs (in seconds)
    |--------------------------------------------------------------------------
    | postid_ttl: 0 = cache forever (recommended — postid never changes).
    |
    | Note: embed/stream URLs are intentionally NOT cached. They are always
    | fetched fresh via a direct AJAX call to khdiamond's admin-ajax.php
    | which is NOT protected by Cloudflare bot protection.
    */
    'cache' => [
        'postid_ttl' => (int) env('CACHE_POSTID_TTL', 0), // 0 = forever
    ],
];
