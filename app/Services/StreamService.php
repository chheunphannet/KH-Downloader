<?php
namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Process\Process as SymfonyProcess;

class StreamService{
    private string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36';

    public function getStream(string $pageUrl, string $movie_type){
      $host = parse_url($pageUrl, PHP_URL_HOST);
      $site = $this->detectSite($pageUrl);
      $movie_name = basename(trim(parse_url($pageUrl, PHP_URL_PATH) ?? '', '/'));
      if (!$site) {
        throw new \Exception("Unsupported site: $host");
      }

      $pageHtml = $this->fetchHtml($pageUrl, "https://$host");

      if($site === 'khdiamond'){
        $postid = $this->detectPostId($pageHtml);

        if($postid){
          $response = $this -> getEmbedUrl($postid, $movie_type, $pageUrl);
          $stream_url = $this->normalizeUrl($response['embed_url']);
          return [
            'site'=> $site,
            'movie_name' => $movie_name,
            'page_url'    => $pageUrl,
            'embed_url' => $response['embed_url'],
            'referer' => $this->getBaseUrl($response['embed_url']),
            'type' => $response['type'],
            'stream_url' =>  $stream_url
          ];
        }
      }

      $iframeSrc = $this->findIframeSrc($pageHtml);
        if (!$iframeSrc) {
            throw new \Exception("No iframe found on page");
        }
      $iframeHtml = $this->fetchHtml($iframeSrc, $pageUrl);

      preg_match('/var playlist = (\[.*?\]);/s', $iframeHtml, $playlistMatch);
      if (empty($playlistMatch[1])) {
          throw new \Exception("No playlist found in iframe");
      }

      $playlist = json_decode($playlistMatch[1], true);
      $source = $playlist[0]['sources'][0] ?? null;
      $streamUrl = $source['file'] ?? null;

      return [
          'site'        => $site,
          'movie_name'  => $movie_name,
          'page_url'    => $pageUrl,
          'referer'     => $this->resolveReferer($site, $streamUrl ?? $iframeSrc),
          // 'iframe_src'  => $iframeSrc,
          'stream_url'  => $streamUrl,
          'thumbnail'   => $playlist[0]['image'] ?? null,
          'subtitles'   => array_map(fn($t) => [
              'label' => $t['label'],
              'file'  => $t['file'],
          ], $playlist[0]['tracks'] ?? []),
      ];
    }

public function analyzeStream(
    string $pageUrl,
    string $movieType = 'movie',
    ?string $ipAddress = null,
    ?string $device = null
): array
{
    $data = $this->getStream($pageUrl, $movieType);

    $this->updateOrInsertSiteMetrics($data['site']);
    $this->insertUserLog(
        $ipAddress ?? '',
        $device ?? '',
        $data['site'],
        $pageUrl
    );

    $command = [
        $this->resolveYtDlpBinary(),
        '--add-header', "Referer: {$data['referer']}",
        '-j',
        $data['stream_url'],
    ];

    logger()->debug('Running yt-dlp', [
        'stream_url' => $data['stream_url'],
        'referer'    => $data['referer'],
        'command'    => $command,
    ]);

    $result = Process::env($this->ytDlpEnvironment())->run($command);

    logger()->debug('yt-dlp result', [
        'successful'   => $result->successful(),
        'exit_code'    => $result->exitCode(),
        'error_output' => $result->errorOutput(),
    ]);

    if ($result->failed()) {
        throw new \RuntimeException('yt-dlp failed: ' . $result->errorOutput());
    }

    $output = json_decode($result->output(), true);
    $title = ucwords(str_replace('-', ' ', $data['movie_name'] ?? ''));
    $formats = $output['formats'] ?? [];
    $qualities = $this->extractQualityList($formats);
    $fileSizes = $this->getFileSizesByQuality($formats, $output);
    $downloadLinks = [];

    foreach ($qualities as $q) {
        $fileSize = $fileSizes[$q] ?? null;

        $downloadLinks[(string) $q] = [
            'url' => URL::temporarySignedRoute(
                'video.download', // route name
                now()->addHours(2), 
                [
                    'referer' => $data['referer'],
                    'site'    => $data['site'],
                    'quality' => $q, // No longer hardcoded!
                    'url'     => base64_encode($data['stream_url']),
                    'name'    => $title,
                    'size'    => $fileSize,
                ],
                false
            ),
            'size' => $fileSize,
        ];
    }

    return [
        'site'      => $data['site'],
        'title'     => $title,
        'embed_url' => $data['embed_url'] ?? null,
        'can_watch' => !empty($data['embed_url']),
        'subtitles' => $data['subtitles'] ?? null,
        'referer'   => $data['referer'] ?? null,
        'links'     => $downloadLinks,
    ];
}

private function getFileSizesByQuality(array $formats, array $metadata): array
{
    $duration = isset($metadata['duration']) && is_numeric($metadata['duration'])
        ? (float) $metadata['duration']
        : null;
    $bestAudioSize = $this->getBestAudioSize($formats, $duration);
    $sizes = [];

    foreach ($formats as $format) {
        if (!isset($format['height']) || !is_numeric($format['height'])) {
            continue;
        }

        $hasVideo = $this->formatHasVideo($format);
        $hasAudio = $this->formatHasAudio($format);

        if (!$hasVideo) {
            continue;
        }

        $formatSize = $this->resolveFormatSize($format, $duration);

        if ($formatSize === null) {
            continue;
        }

        $height = (int) $format['height'];
        $bytes = $formatSize;

        if (!$hasAudio && $bestAudioSize !== null) {
            $bytes += $bestAudioSize;
        }

        $score = $this->formatScore($format);

        if (!isset($sizes[$height]) || $score > $sizes[$height]['score']) {
            $sizes[$height] = [
                'bytes' => $bytes,
                'score' => $score,
            ];
        }
    }

    krsort($sizes);

    foreach ($sizes as $height => $size) {
        $sizes[$height] = $this->formatBytes($size['bytes']);
    }

    return $sizes;
}

private function getBestAudioSize(array $formats, ?float $duration): ?int
{
    $best = null;

    foreach ($formats as $format) {
        $hasVideo = $this->formatHasVideo($format);
        $hasAudio = $this->formatHasAudio($format);

        if ($hasVideo || !$hasAudio) {
            continue;
        }

        $size = $this->resolveFormatSize($format, $duration);

        if ($size === null) {
            continue;
        }

        $score = $this->formatScore($format);

        if ($best === null || $score > $best['score']) {
            $best = [
                'bytes' => $size,
                'score' => $score,
            ];
        }
    }

    return $best['bytes'] ?? null;
}

private function resolveFormatSize(array $format, ?float $duration): ?int
{
    foreach (['filesize', 'filesize_approx'] as $key) {
        if (isset($format[$key]) && is_numeric($format[$key]) && (int) $format[$key] > 0) {
            return (int) $format[$key];
        }
    }

    $duration = $this->resolveFormatDuration($format, $duration);

    if ($duration === null || $duration <= 0) {
        return null;
    }

    foreach (['tbr', 'vbr', 'abr'] as $key) {
        if (isset($format[$key]) && is_numeric($format[$key]) && (float) $format[$key] > 0) {
            return (int) round(((float) $format[$key] * 1000 / 8) * $duration);
        }
    }

    return null;
}

private function resolveFormatDuration(array $format, ?float $fallbackDuration): ?float
{
    if ($fallbackDuration !== null && $fallbackDuration > 0) {
        return $fallbackDuration;
    }

    if (isset($format['duration']) && is_numeric($format['duration']) && (float) $format['duration'] > 0) {
        return (float) $format['duration'];
    }

    if (!isset($format['fragments']) || !is_array($format['fragments'])) {
        return null;
    }

    $duration = 0.0;

    foreach ($format['fragments'] as $fragment) {
        if (isset($fragment['duration']) && is_numeric($fragment['duration'])) {
            $duration += (float) $fragment['duration'];
        }
    }

    return $duration > 0 ? $duration : null;
}

private function formatHasVideo(array $format): bool
{
    if (array_key_exists('vcodec', $format) && $format['vcodec'] !== null) {
        return $format['vcodec'] !== 'none';
    }

    if (isset($format['video_ext'])) {
        return $format['video_ext'] !== 'none';
    }

    return isset($format['height']) || isset($format['width']);
}

private function formatHasAudio(array $format): bool
{
    if (array_key_exists('acodec', $format) && $format['acodec'] !== null) {
        return $format['acodec'] !== 'none';
    }

    if (isset($format['audio_ext'])) {
        return $format['audio_ext'] !== 'none';
    }

    return isset($format['abr']) && is_numeric($format['abr']) && (float) $format['abr'] > 0;
}

private function formatScore(array $format): float
{
    foreach (['tbr', 'vbr', 'abr', 'quality'] as $key) {
        if (isset($format[$key]) && is_numeric($format[$key])) {
            return (float) $format[$key];
        }
    }

    return 0.0;
}

private function formatBytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $size = max($bytes, 0);
    $unit = 0;

    while ($size >= 1024 && $unit < count($units) - 1) {
        $size /= 1024;
        $unit++;
    }

    if ($unit === 0) {
        return $size . ' ' . $units[$unit];
    }

    if ($unit <= 2) {
        return round($size) . ' ' . $units[$unit];
    }

    return rtrim(rtrim(number_format($size, 2, '.', ''), '0'), '.') . ' ' . $units[$unit];
}

public function createDownloadStreamCallback(
    string $streamUrl,
    int|string|null $quality,
    string $referer
): Closure
{
    return function () use ($streamUrl, $quality, $referer) {
        $process = null;
        $clientAborted = false;

       if (ob_get_level() > 0) {
            ob_flush();
        }

        Redis::incr('active_downloads_count');
        Redis::expire('active_downloads_count', 21600);

        try {
            set_time_limit(0);
            ignore_user_abort(true);

            $tempDirectory = $this->downloadTempDirectory();

            $process = new SymfonyProcess(
                $this->buildDownloadCommand($streamUrl, $quality, $referer),
                $tempDirectory,
                $this->ytDlpEnvironment($tempDirectory)
            );

            $process->setTimeout(null);
            $process->start();

            foreach ($process as $type => $buffer) {
                if ($type !== $process::OUT || $buffer === '') {
                    continue;
                }

                echo $buffer;


                flush();

                if (connection_aborted()) {
                    $clientAborted = true;
                    break;
                }
            }

            if (!$clientAborted && !$process->isSuccessful()) {
                logger()->error('yt-dlp download failed', [
                    'command' => $this->buildDownloadCommand($streamUrl, $quality, $referer),
                    'error_output' => $process->getErrorOutput(),
                    'exit_code' => $process->getExitCode(),
                ]);
            }
        } finally {
            if ($process?->isRunning()) {
                $process->stop(1);
            }

            $current = Redis::decr('active_downloads_count');

            if ($current <= 0) {
                Redis::del('active_downloads_count');
            }
        }
    };
}

private function updateOrInsertSiteMetrics(string $site): void
{
  DB::table('site_metrics')->updateOrInsert(
    ['site_name' => $site], 
    ['total_processes' => DB::raw('total_processes + 1'), 'updated_at' => now()]
  );
}

private function insertUserLog(string $ipAddress, string $device, string $site, string $pageUrl): void
{
  DB::table('user_logs')->insert([
      'ip_address' => $ipAddress,
      'user_agent'     => $device,
      'site_requested' => $site,
      'page_url'   => $pageUrl,
      'created_at' => now(),
  ]);
}

private function detectSite(string $url): ?string{ //can return string or null
    if (str_contains($url, 'khfullhd')) return 'khfullhd';
    if (str_contains($url, 'khanime')) return 'khanime';
    if (str_contains($url, 'khdiamond')) return 'khdiamond';
    return null;
  }

private function resolveYtDlpBinary(): string
{
    return config('streaming.yt_dlp_binary', 'yt-dlp');
}

private function ytDlpEnvironment(?string $tempDirectory = null): array
{
    $path = getenv('PATH') ?: '';
    $tempDirectory ??= $this->downloadTempDirectory();

    if (PHP_OS_FAMILY === 'Windows') {
        return [
            'PATH'       => $path,
            'TMP'        => $tempDirectory,
            'TEMP'       => $tempDirectory,
            'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\\Windows',
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
            'WINDIR'     => getenv('WINDIR') ?: 'C:\\Windows',
        ];
    }

    return [
        'PATH'   => $path,
        'HOME'   => $tempDirectory,
        'TMPDIR' => $tempDirectory,
        'TMP'    => $tempDirectory,
        'TEMP'   => $tempDirectory,
    ];
}

private function extractQualityList(array $formats): array
{
    $qualities = array_filter(
        array_unique(
            array_map(
                fn($f) => isset($f['height']) && is_numeric($f['height'])
                    ? (int) $f['height']
                    : null,
                $formats
            )
        ),
        fn($q) => $q !== null
    );

    rsort($qualities);

    return array_values($qualities);
}

private function buildFormatSelector(int|string|null $quality): string
{
    $height = is_numeric($quality) ? (int) $quality : 720;

    return implode('/', [
        "best[height<={$height}][vcodec!=none][acodec!=none]",
        "best[height<={$height}]",
        'best',
    ]);
}

private function buildDownloadCommand(
    string $streamUrl,
    int|string|null $quality,
    string $referer
): array
{
    return [
        $this->resolveYtDlpBinary(),
        '--add-header', "Referer: {$referer}",
        '-f', $this->buildFormatSelector($quality),
        '--paths', 'temp:' . $this->downloadTempDirectory(),
        '--no-cache-dir',
        '--no-part',
        '-o', '-',
        $streamUrl,
    ];
}

private function downloadTempDirectory(): string
{
    $path = (string) config('streaming.download_temp_path', storage_path('app/yt-dlp-temp'));

    if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
        throw new \RuntimeException("Unable to create download temp directory: {$path}");
    }

    if (!is_writable($path)) {
        throw new \RuntimeException("Download temp directory is not writable: {$path}");
    }

    return $path;
}

    private function findIframeSrc(string $html): ?string{
      //khfullhd
      if (preg_match('/src="(https:\/\/stream\.khfullhd\.co\/e\/[^"]+)"/', $html, $m)) return $m[1];
      // khanime
      if (preg_match('/src="(https:\/\/stream\.khanime\.co\/e\/[^"]+)"/', $html, $m))  return $m[1];
      return null;
    }

    private function detectPostId(string $html): ?int{
      if (preg_match('/postid-(\d+)/' ,$html, $m)) return $m[1];
      return null;
    }

private function normalizeUrl(string $value): string
{
    // Trim and remove trailing slashes
    $normalized = rtrim(trim($value), '/');

    $parts = parse_url($normalized);
    $scheme = $parts['scheme'] ?? 'https';
    $host   = $parts['host'] ?? '';
    $path   = $parts['path'] ?? '';

    $path = rtrim($path, '/');
    $segments = array_values(array_filter(
        explode('/', $path),
        static fn (string $segment): bool => $segment !== ''
    ));

    // Add "playlist"
    $path = '/playlist/' . implode('/', $segments);
    $normalized = "{$scheme}://{$host}{$path}";

    return rtrim($normalized, '/') . '/master/';
  }

private function getBaseUrl(string $url): string
{
    $parts = parse_url($url);
    $scheme = $parts['scheme'] ?? 'https';
    $host = $parts['host'] ?? '';

    if ($host === '') {
        return '';
    }

    return "{$scheme}://{$host}/";
}

private function resolveReferer(string $site, string $url): string
{
    if ($site === 'khanime' || $site === 'khfullhd') {
        return 'https://stream.khanime.co/';
    }

    return $this->getBaseUrl($url);
}

private function getEmbedUrl(int $postid, string $movie_type, string $pageUrl) {
  $response = Http::withHeaders([
          'Referer' => $pageUrl,
          'User-Agent' => $this->userAgent
      ])->withoutVerifying()->asForm()->post(
          "https://khdiamond.net/wp-admin/admin-ajax.php",
          [
              'action' => 'doo_player_ajax',
              'post'   => $postid,
              'nume'   => 1,
              'type'   => $movie_type,
          ]
      );

      if (!$response->successful()) {
          throw new \Exception("khdiamond ajax request failed with status " . $response->status());
      }

      $payload = $response->json();

      if (!is_array($payload)) {
          throw new \Exception('khdiamond ajax response was not valid JSON');
      }

      if (empty($payload['embed_url'])) {
          throw new \Exception('khdiamond ajax response did not contain embed_url');
      }

      return $payload;
}

private function fetchHtml(string $url, string $referer): string
{
  $response = Http::withHeaders([
     'User-Agent' => $this->userAgent,
     'Referer'    => $referer,
  ])->withoutVerifying()->get($url);

  if(!$response->ok()){
    throw new \Exception("Failed to fetch: $url (status {$response->status()})");
  }
  return $response->body();
}
}
