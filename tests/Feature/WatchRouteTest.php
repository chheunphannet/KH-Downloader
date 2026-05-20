<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use App\Services\StreamService;
use Tests\TestCase;

class WatchRouteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        try {
            Redis::flushall();
        } catch (\Throwable $e) {
        }
    }

    public function test_watch_page_requires_supported_site(): void
    {
        $response = $this->get('/watch/unsupported-site/my-slug');
        $response->assertStatus(404);
    }

    public function test_watch_page_resolves_and_caches_metadata(): void
    {
        $mockStreamData = [
            'site' => 'khdiamond',
            'movie_name' => 'my-awesome-movie-slug',
            'type' => 'movie',
            'next_url' => 'https://khdiamond.net/next-episode-url',
            'thumbnail' => 'https://khdiamond.net/poster.jpg',
            'embed_url' => 'https://player.khdiamond.net/embed/123/abc/',
        ];

        // Define all expectations on a single mock upfront to avoid container singleton re-injection issues
        $this->mock(StreamService::class, function ($mock) use ($mockStreamData) {
            $mock->shouldReceive('reconstructUrl')
                ->twice()
                ->with('khdiamond', 'my-awesome-movie-slug')
                ->andReturn('https://khdiamond.net/my-awesome-movie-slug');

            $mock->shouldReceive('getStream')
                ->once()
                ->with('https://khdiamond.net/my-awesome-movie-slug', 'movie')
                ->andReturn($mockStreamData);
        });

        // 1. First load: Scrapes and caches permanently
        $response = $this->get('/watch/khdiamond/my-awesome-movie-slug');
        $response->assertOk();
        $response->assertViewHas('meta');

        // Check it cached in Redis
        $cachedJson = Redis::get('video:meta:khdiamond:my-awesome-movie-slug');
        $this->assertNotNull($cachedJson);
        $cached = json_decode($cachedJson, true);
        $this->assertEquals('My Awesome Movie Slug', $cached['title']);
        $this->assertEquals('movie', $cached['type']);

        // 2. Second load: Uses cached metadata (should not trigger getStream)
        $response2 = $this->get('/watch/khdiamond/my-awesome-movie-slug');
        $response2->assertOk();
    }

    public function test_api_streams_endpoint_caches_downloads_and_refreshes_embed(): void
    {
        $mockStreamResult = [
            'site' => 'khdiamond',
            'title' => 'My Awesome Movie Slug',
            'embed_url' => 'https://player.khdiamond.net/embed/123/abc/',
            'can_watch' => true,
            'subtitles' => [],
            'referer' => 'https://player.khdiamond.net/',
            'stream_url' => 'https://player.khdiamond.net/playlist/master.m3u8',
            'links' => [
                '1080' => [
                    'url' => 'https://signed-download-link.com',
                    'size' => '1.2 GB'
                ]
            ],
            'next_url' => null,
        ];

        // Seed metadata cache with postid so embed refresh can work
        Redis::set('video:meta:khdiamond:my-awesome-movie-slug', json_encode([
            'site' => 'khdiamond',
            'slug' => 'my-awesome-movie-slug',
            'title' => 'My Awesome Movie Slug',
            'type' => 'movie',
            'postid' => 12345,
            'movie_type' => 'movie',
            'page_url' => 'https://khdiamond.net/my-awesome-movie-slug',
        ]));

        $this->mock(StreamService::class, function ($mock) use ($mockStreamResult) {
            // First call: full analysis (no cache yet)
            $mock->shouldReceive('reconstructUrl')
                ->with('khdiamond', 'my-awesome-movie-slug')
                ->andReturn('https://khdiamond.net/my-awesome-movie-slug');

            $mock->shouldReceive('analyzeStream')
                ->once()
                ->andReturn($mockStreamResult);

            // Second call: embed refresh using cached postid (no re-analysis)
            $mock->shouldReceive('refreshKhdiamondEmbed')
                ->once()
                ->with(12345, 'movie', 'https://khdiamond.net/my-awesome-movie-slug')
                ->andReturn(['embed_url' => 'https://player.khdiamond.net/embed/999/fresh/']);
        });

        // 1. First API call: full analysis, downloads cached for 2h
        $response = $this->getJson('/api/streams/khdiamond/my-awesome-movie-slug');
        $response->assertOk()
            ->assertJsonPath('title', 'My Awesome Movie Slug');

        $this->assertNotNull(Redis::get('video:streams:khdiamond:my-awesome-movie-slug'));

        // 2. Second API call: returns cached downloads + FRESH embed_url
        $response2 = $this->getJson('/api/streams/khdiamond/my-awesome-movie-slug');
        $response2->assertOk()
            ->assertJsonPath('title', 'My Awesome Movie Slug')
            ->assertJsonPath('embed_url', 'https://player.khdiamond.net/embed/999/fresh/')
            ->assertJsonPath('can_watch', true);
    }

    public function test_watch_page_normalizes_trailing_slashes(): void
    {
        $mockStreamData = [
            'site' => 'khdiamond',
            'movie_name' => 'sitaare-zameen-par',
            'type' => 'movie',
            'next_url' => null,
            'thumbnail' => null,
            'embed_url' => 'https://player.khdiamond.net/embed/123/abc/',
        ];

        $this->mock(StreamService::class, function ($mock) use ($mockStreamData) {
            $mock->shouldReceive('reconstructUrl')
                ->once()
                ->with('khdiamond', 'sitaare-zameen-par')
                ->andReturn('https://khdiamond.net/sitaare-zameen-par');

            $mock->shouldReceive('getStream')
                ->once()
                ->with('https://khdiamond.net/sitaare-zameen-par', 'movie')
                ->andReturn($mockStreamData);
        });

        // Request with trailing slash
        $response = $this->get('/watch/khdiamond/sitaare-zameen-par/');
        $response->assertOk();
        $response->assertViewHas('meta');

        $meta = $response->viewData('meta');
        $this->assertEquals('sitaare-zameen-par', $meta['slug']);
        $this->assertEquals('khdiamond', $meta['site']);
    }

    public function test_watch_page_renders_data_attributes_in_html(): void
    {
        $mockStreamData = [
            'site' => 'khanime',
            'movie_name' => 'dr-stone-season-1-episode-6',
            'type' => 'video',
            'next_url' => null,
            'thumbnail' => null,
            'embed_url' => null,
        ];

        $this->mock(StreamService::class, function ($mock) use ($mockStreamData) {
            $mock->shouldReceive('reconstructUrl')
                ->once()
                ->with('khanime', 'episode/dr-stone-season-1-episode-6')
                ->andReturn('https://khanime.co/episode/dr-stone-season-1-episode-6');

            $mock->shouldReceive('getStream')
                ->once()
                ->andReturn($mockStreamData);
        });

        $response = $this->get('/watch/khanime/episode/dr-stone-season-1-episode-6/');
        $response->assertOk();

        // Verify body tag has proper data-* attributes that JS relies on
        $response->assertSee('data-page="watch"', false);
        $response->assertSee('data-site="khanime"', false);
        $response->assertSee('data-slug="episode/dr-stone-season-1-episode-6"', false);

        // Verify khanime does NOT show embed iframe (only khdiamond has that)
        $response->assertDontSee('id="watchEmbedFrame"', false);
        // Verify it shows the download placeholder instead
        $response->assertSee('id="directStreamPlaceholder"', false);
        // Verify khanime skeleton says "download links" not "stream credentials"
        $response->assertSee('Preparing download links...', false);
    }
}
