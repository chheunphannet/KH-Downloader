<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Services\StreamService;
use Illuminate\Support\Facades\Redis;

class PageController extends Controller
{
    public function __construct(private StreamService $streamService) {}

    public function watch(string $site, string $slug): View
    {
        $validSites = ['khdiamond', 'khanime', 'khfullhd'];
        if (!in_array($site, $validSites, true)) {
            abort(404, 'Unsupported video platform');
        }

        $slug = trim($slug, '/');

        $pageUrl = $this->streamService->reconstructUrl($site, $slug);
        $cacheKey = "video:meta:{$site}:{$slug}";
        $metaJson = Redis::get($cacheKey);

        if ($metaJson) {
            $meta = json_decode($metaJson, true);
            $meta['site'] = $site;
            $meta['slug'] = $slug;
        } else {
            try {
                $streamData = $this->streamService->getStream($pageUrl, 'movie');
                $title = ucwords(str_replace('-', ' ', $streamData['movie_name'] ?? ''));

                $meta = [
                    'site' => $site,
                    'slug' => $slug,
                    'title' => $title,
                    'type' => $streamData['type'] ?? ($streamData['site'] === 'khdiamond' ? 'movie' : 'video'),
                    'next_url' => $streamData['next_url'] ?? null,
                    'thumbnail' => $streamData['thumbnail'] ?? null,
                    'page_url' => $pageUrl,
                    'postid' => $streamData['postid'] ?? null,
                    'movie_type' => $streamData['movie_type'] ?? null,
                ];

                Redis::set($cacheKey, json_encode($meta));
            } catch (\Throwable $e) {
                logger()->error('Metadata fetch failed on watch', ['url' => $pageUrl, 'error' => $e->getMessage()]);
                // Instead of abort(404) which looks like a routing error, pass the error to the view
                $meta = [
                    'site' => $site,
                    'slug' => $slug,
                    'title' => 'Error Loading Video',
                    'error' => 'Unable to scrape video metadata. The target site might be blocking your VPS IP, or the video link is invalid. Details: ' . $e->getMessage(),
                    'page_url' => $pageUrl,
                ];
            }
        }

        return view('watch', compact('meta'));
    }

    public function faq(): View
    {
        return view('pages.faq');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }
}

