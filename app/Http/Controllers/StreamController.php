<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\StreamService;
use Illuminate\Support\Facades\Redis;

class StreamController extends Controller{
  public function __construct(private StreamService $streamService) {}

  //POST /api/analyze
   public function analyze(Request $request)
    {
      $request->validate([
          'url' => 'required|url',
          'type' => 'nullable|string',
      ]);

      try {
          return response()->json(
            $this->streamService->analyzeStream(
              $request->input('url'),
              $request->input('type') ?? 'movie',
              $request->ip(),
              $request->userAgent()
            )
          );
      } catch (\Throwable $e) {
          return response()->json(['error' => $e->getMessage()], 400);
      }
  }

// GET /download
public function download(Request $request)
{
    if (session_id()) {
        session_write_close();
    }
    
    // $site = $request->query('site');
    $quality = $request->query('quality');
    $encodedUrl = $request->query('url');
    $streamUrl = is_string($encodedUrl) ? base64_decode($encodedUrl, true) : false;
    $referer = (string) $request->query('referer', '');
    $movieName = $request->query('name');
    $fileSize = $request->query('size');

    if ($streamUrl === false || !filter_var($streamUrl, FILTER_VALIDATE_URL)) {
        return response()->json(['error' => 'Invalid or expired download link. Process the video link again.'], 422);
    }

    // 2. Check Redis Concurrency Limit
    $count = max((int) Redis::get('active_downloads_count'), 0);
    if ($count >= 5) {
        return response()->json(['error' => 'Server Full (5/5). Please try again in a moment.'], 429);
    }

    $fileNameParts = array_filter([
        $movieName ?: 'video',
        $quality ? "{$quality}p" : null,
        $fileSize,
    ]);
    $fileName = trim(preg_replace('/[\\\\\/:*?"<>|]+/', '', implode(' ', $fileNameParts))) ?: 'video';
    $fileName .= '.mp4';

//     return response()->stream(
//     function () {
//         logger()->info('TEST: stream callback fired');
//         while (ob_get_level() > 0) ob_end_clean();
//         echo str_repeat('A', 1024 * 1024); // 1MB of data
//         flush();
//         logger()->info('TEST: stream callback done');
//     },
//     200,
//     [
//         'Content-Type' => 'application/octet-stream',
//         'Content-Disposition' => 'attachment; filename="test.bin"',
//         'X-Accel-Buffering' => 'no',
//     ]
// );

    return response()->stream(
        $this->streamService->createDownloadStreamCallback($streamUrl, $quality, $referer),
        200,
        [
        'Content-Description' => 'File Transfer',
        'Content-Type' => 'application/octet-stream',
        'Content-Disposition' => 'attachment; filename="' . addcslashes($fileName, '"\\') . '"',
        'Content-Transfer-Encoding' => 'binary',
        'Cache-Control' => 'no-store, no-cache, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
        'X-Accel-Buffering' => 'no',
        'X-Content-Type-Options' => 'nosniff',
        ]
    );
}

    public function serverStatus()
    {
        $current = max((int) Redis::get('active_downloads_count'), 0);
        return response()->json([
            'current' => $current,
            'max' => 5,
            'available' => $current < 5,
            'wait_list' => $current >= 5 ? ($current - 4) : 0 
        ]);
    }
}
