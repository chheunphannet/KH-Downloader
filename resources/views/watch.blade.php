@extends('layouts.app')

@section('page_name', 'watch')
@section('page_site', e($meta['site']))
@section('page_slug', e($meta['slug']))
@section('title', e($meta['title']) . ' - Stream & Download | KH Downloader')
@section('meta_description', 'Watch ' . e($meta['title']) . ' online or download in high quality for free on KH Downloader. Supports KHDiamond, KHAnime, and KHFullHD.')

@section('content')
    <div id="toast" class="toast hidden" role="status" aria-live="polite"></div>
    <iframe id="downloadFrame" name="downloadFrame" class="hidden" title="Download"></iframe>

    <header class="fixed inset-x-0 top-0 z-30 border-b border-zinc-200/70 bg-zinc-50/85 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/85">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="/" class="flex items-center gap-2 text-sm font-semibold tracking-normal text-zinc-800 dark:text-zinc-100">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                Back to Search
            </a>
            <div class="flex items-center gap-3">
                <a href="/faq" class="hidden text-sm font-medium text-zinc-500 transition hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-50 sm:inline">FAQ</a>
                <div id="serverStatusPill" class="status-pill" aria-live="polite">
                    <span class="status-dot bg-zinc-400"></span>
                    <span id="serverStatusText">Checking</span>
                    <span id="serverLoadText" class="font-semibold text-zinc-900 dark:text-zinc-100">0 / 5 Slots</span>
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto min-h-screen max-w-6xl px-4 pb-16 pt-24 sm:px-6 sm:pt-28">
        <!-- Video Header Info -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between border-b border-zinc-200/60 pb-6 dark:border-zinc-800/60">
            <div class="min-w-0 flex-1">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    @php
                        $siteClasses = [
                            'khdiamond' => 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-200',
                            'khanime' => 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-200',
                            'khfullhd' => 'bg-violet-100 text-violet-800 dark:bg-violet-500/15 dark:text-violet-200'
                        ];
                        $siteNames = [
                            'khdiamond' => 'KHDiamond',
                            'khanime' => 'KHAnime',
                            'khfullhd' => 'KHFullHD'
                        ];
                        $class = $siteClasses[$meta['site']] ?? 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200';
                        $name = $siteNames[$meta['site']] ?? 'Unknown';
                    @endphp
                    <span class="site-badge {{ $class }}">{{ $name }}</span>
                    <span class="site-badge bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 uppercase tracking-wider text-[10px]">{{ $meta['type'] ?? 'video' }}</span>
                </div>
                <h1 class="text-2xl font-bold tracking-normal text-zinc-950 dark:text-white sm:text-3xl lg:text-4xl">{{ $meta['title'] }}</h1>
            </div>
            
            @if(!empty($meta['next_url']))
                <button type="button" id="watchNextEpisodeButton" class="primary-button justify-center rounded-lg whitespace-nowrap self-start" data-next-url="{{ $meta['next_url'] }}">
                    Next Episode
                </button>
            @endif
        </div>

        <!-- Main Content Area: Player & Downloads -->
        @if(isset($meta['error']))
            <div class="rounded-xl border border-red-200 bg-red-50 p-8 text-center shadow-sm dark:border-red-900/30 dark:bg-red-500/10">
                <svg class="mx-auto h-12 w-12 text-red-500 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <h3 class="text-xl font-bold text-red-800 dark:text-red-200">Failed to Load Video</h3>
                <p class="mt-2 text-sm text-red-600 dark:text-red-400 max-w-2xl mx-auto">{{ $meta['error'] }}</p>
                <a href="/" class="primary-button mt-6 inline-flex">Go back to search</a>
            </div>
            <!-- Hack to prevent initWatchPage from trying to load streams -->
            <script>
                document.body.removeAttribute('data-site');
            </script>
        @else
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Left Column: Video Player Card -->
            <div class="lg:col-span-2">
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="aspect-video bg-black relative flex items-center justify-center">
                        
                        <!-- Premium Loading Skeleton -->
                        @if($meta['site'] === 'khdiamond')
                            <div id="playerSkeleton" class="absolute inset-0 flex flex-col items-center justify-center bg-zinc-950 p-6 text-center z-10">
                                <div class="h-12 w-12 rounded-full border-4 border-teal-500 border-t-transparent animate-spin mb-4"></div>
                                <h3 class="text-lg font-semibold text-zinc-200 animate-pulse">Establishing secure connection...</h3>
                                <p class="text-sm text-zinc-400 mt-2 max-w-sm">Fetching and decrypting active stream credentials from {{ $name }} servers.</p>
                            </div>
                        @else
                            <div id="playerSkeleton" class="absolute inset-0 flex flex-col items-center justify-center bg-zinc-950 p-6 text-center z-10">
                                <div class="h-12 w-12 rounded-full border-4 border-teal-500 border-t-transparent animate-spin mb-4"></div>
                                <h3 class="text-lg font-semibold text-zinc-200 animate-pulse">Preparing download links...</h3>
                                <p class="text-sm text-zinc-400 mt-2 max-w-sm">Fetching high-speed download credentials from {{ $name }} servers.</p>
                            </div>
                        @endif

                        <!-- Error State (hidden by default) -->
                        <div id="playerError" class="absolute inset-0 hidden flex flex-col items-center justify-center bg-zinc-950 p-6 text-center z-10">
                            <svg class="h-12 w-12 text-red-500 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-zinc-200">Failed to load video stream</h3>
                            <p class="text-sm text-zinc-400 mt-2 max-w-sm">This link may be temporarily down or requires a new extraction.</p>
                            <button id="playerRetryButton" type="button" class="primary-button mt-4 justify-center rounded-lg px-4 py-2 text-xs">
                                Retry Connection
                            </button>
                        </div>

                        <!-- Direct Playback IFrame Container (For khdiamond only) -->
                        @if($meta['site'] === 'khdiamond')
                            <iframe id="watchEmbedFrame" title="Video player" class="absolute inset-0 h-full w-full hidden" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
                        @else
                            <div id="directStreamPlaceholder" class="absolute inset-0 hidden flex flex-col items-center justify-center bg-zinc-950 p-6 text-center">
                                <svg class="h-16 w-16 text-zinc-500 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                                </svg>
                                <h3 class="text-lg font-semibold text-zinc-200">Direct Download & Playback</h3>
                                <p class="text-sm text-zinc-400 mt-2 max-w-md">Streaming is optimized for direct download. Get the high-speed video files from the download panel on the right.</p>
                            </div>
                        @endif

                    </div>
                    <div class="p-4 bg-zinc-50/50 dark:bg-zinc-900/50 border-t border-zinc-200/80 dark:border-zinc-800/80">
                        <div class="flex items-center gap-3">
                            <span class="relative flex h-2 w-2">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                            </span>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Stream links dynamically verified and cached using secure SSL channels.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Download Card -->
            <div>
                <div class="option-panel h-full flex flex-col">
                    <div class="mb-4 flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">High-Speed Downloads</h3>
                    </div>

                    <!-- Shimmer Loader for Downloads -->
                    <div id="downloadsSkeleton" class="flex-1 flex flex-col gap-3 animate-pulse">
                        <div class="h-12 w-full bg-zinc-200 dark:bg-zinc-800 rounded-lg"></div>
                        <div class="h-12 w-full bg-zinc-200 dark:bg-zinc-800 rounded-lg"></div>
                        <div class="h-12 w-full bg-zinc-200 dark:bg-zinc-800 rounded-lg"></div>
                    </div>

                    <!-- Active Downloads List -->
                    <div id="downloadsList" class="hidden flex-1 flex flex-col gap-3"></div>
                </div>
            </div>
        </div>
        @endif

        <!-- Subtitles drawer if present -->
        <div id="watchSubtitlesSection" class="mt-8 border-t border-zinc-200 pt-5 dark:border-zinc-800 hidden">
            <h3 class="text-sm font-semibold uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400 mb-4">Available Captions</h3>
            <div id="watchSubtitlesList" class="flex flex-wrap gap-2"></div>
        </div>
    </main>
@endsection
