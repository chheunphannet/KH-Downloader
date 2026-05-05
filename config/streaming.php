<?php

return [
    'yt_dlp_binary' => env('YT_DLP_BINARY', 'yt-dlp'),
    'download_temp_path' => env('DOWNLOAD_TEMP_PATH', storage_path('app/yt-dlp-temp')),
];
