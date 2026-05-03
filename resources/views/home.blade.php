<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Link Processor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-page="home" class="min-h-screen bg-zinc-50 text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-50">
    <div id="toast" class="toast hidden" role="status" aria-live="polite"></div>

    <header class="fixed inset-x-0 top-0 z-30 border-b border-zinc-200/70 bg-zinc-50/85 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/85">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="/" class="text-sm font-semibold tracking-normal text-zinc-800 dark:text-zinc-100">KH Downloader</a>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.metrics') }}" class="hidden text-sm font-medium text-zinc-500 transition hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-50 sm:inline">Admin</a>
                <div id="serverStatusPill" class="status-pill" aria-live="polite">
                    <span class="status-dot bg-zinc-400"></span>
                    <span id="serverStatusText">Checking</span>
                    <span id="serverLoadText" class="font-semibold text-zinc-900 dark:text-zinc-100">0 / 5 Slots</span>
                </div>
            </div>
        </div>
    </header>

    <main id="downloaderApp" class="min-h-screen px-4 pb-16 pt-28 sm:px-6 sm:pt-36">
        <section class="mx-auto flex max-w-4xl flex-col items-center text-center">
            <div class="mb-7 rounded-lg border border-zinc-200 bg-white p-2 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <img
                    src="{{ asset('images/original-6cb36d3fe1718ccd11d403ec91810aac.webp') }}"
                    alt="KH Downloader"
                    class="h-16 w-auto max-w-[220px] rounded-md object-contain sm:h-20"
                    decoding="async"
                >
            </div>

            <h1 class="max-w-3xl text-balance text-4xl font-bold tracking-normal text-zinc-950 dark:text-white sm:text-6xl">Video Link Processor</h1>

            <form id="analyzeForm" class="search-shell mt-8 w-full" autocomplete="off">
                <label for="urlInput" class="sr-only">Video page URL</label>
                <input
                    id="urlInput"
                    name="url"
                    type="url"
                    required
                    placeholder="Paste a video link"
                    class="min-w-0 flex-1 bg-transparent px-5 py-4 text-base text-zinc-950 outline-none placeholder:text-zinc-400 dark:text-white dark:placeholder:text-zinc-500 sm:text-lg"
                >
                <button id="processButton" type="submit" class="primary-button m-1 shrink-0">
                    <span class="button-label">Process Link</span>
                    <span class="button-spinner hidden" aria-hidden="true"></span>
                </button>
            </form>

            <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Supported sites: KHDiamond, KHAnime, KHFullHD</p>

            <div id="capacityBanner" class="mt-6 hidden w-full rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-left text-sm font-medium text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
                Server is currently at max capacity. Your download will be queued.
            </div>

            <section id="resultCard" class="result-card mt-8 hidden w-full text-left" aria-live="polite"></section>
        </section>
    </main>

    <div id="watchModal" class="modal-shell hidden" role="dialog" aria-modal="true" aria-label="Watch online">
        <div class="modal-backdrop" data-close-watch></div>
        <div class="modal-panel">
            <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-800">
                <h2 id="watchTitle" class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">Watch Online</h2>
                <button type="button" class="icon-button" data-close-watch aria-label="Close video">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m6.75 6.75 10.5 10.5M17.25 6.75 6.75 17.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            <div class="aspect-video bg-black">
                <iframe id="watchFrame" title="Video player" class="h-full w-full" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</body>
</html>
