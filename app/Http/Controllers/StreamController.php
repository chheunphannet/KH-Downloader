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
    $streamUrl = base64_decode($encodedUrl);
    $referer = $request->query('referer');
    $movieName = $request->query('name');
    $fileSize = $request->query('size');

    // 2. Check Redis Concurrency Limit
    $count = (int) Redis::get('active_downloads_count');
    if ($count >= 5) {
        return response()->json(['error' => 'Server Full (5/5). Please try again in a moment.'], 429);
    }

    $fileNameParts = array_filter([
        $movieName ?: 'video',
        $quality ? "{$quality}p" : null,
        $fileSize,
    ]);
    $fileName = preg_replace('/[\\\\\/:*?"<>|]+/', '', implode(' ', $fileNameParts)) . '.mp4';

    return response()->stream(
        $this->streamService->createDownloadStreamCallback($streamUrl, $quality, $referer),
        200,
        [
        'Content-Type' => 'video/mp4',
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        'Cache-Control' => 'no-cache',
        'X-Accel-Buffering' => 'no', //Tells Nginx not to buffer the video
        ]
    );
}

    public function serverStatus()
    {
        $current = (int) Redis::get('active_downloads_count') ?: 0;
        return response()->json([
            'current' => $current,
            'max' => 5,
            'available' => $current < 5,
            'wait_list' => $current >= 5 ? ($current - 4) : 0 
        ]);
    }
}
