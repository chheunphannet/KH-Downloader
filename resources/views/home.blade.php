@extends('layouts.app')

@section('body_attributes', 'data-page="home"')

@section('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "WebApplication",
  "name": "KH Downloader",
  "url": "{{ url('/') }}",
  "description": "KH Downloader is a fast, high quality video downloader for KHDiamond, KHAnime, and KHFullHD links with no registration required.",
  "applicationCategory": "MultimediaApplication",
  "operatingSystem": "All",
  "offers": {
    "@@type": "Offer",
    "price": "0",
    "priceCurrency": "USD"
  }
}
</script>
@endsection

@section('content')
    <div id="toast" class="toast hidden" role="status" aria-live="polite"></div>
    <iframe id="downloadFrame" name="downloadFrame" class="hidden" title="Download"></iframe>

    <header class="fixed inset-x-0 top-0 z-30 border-b border-zinc-200/70 bg-zinc-50/85 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/85">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="/" class="text-sm font-semibold tracking-normal text-zinc-800 dark:text-zinc-100">KH Downloader</a>
            <div class="flex items-center gap-3">
                <a href="#how-it-works" class="hidden text-sm font-medium text-zinc-500 transition hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-50 sm:inline">How it works</a>
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
                    src="{{ asset('images/logo.webp') }}"
                    alt="KH Downloader"
                    class="h-16 w-auto max-w-[220px] rounded-md object-contain sm:h-20"
                    decoding="async"
                >
            </div>

            <h1 class="max-w-3xl text-balance text-4xl font-bold tracking-normal text-zinc-950 dark:text-white sm:text-6xl">Ultimate Video Downloader</h1>

            <form id="analyzeForm" class="search-shell mt-8 w-full" autocomplete="off" onsubmit="return false">
                <label for="urlInput" class="sr-only">Video page URL</label>
                <input
                    id="urlInput"
                    name="url"
                    type="text"
                    inputmode="url"
                    required
                    placeholder="Paste a video link"
                    class="min-w-0 flex-1 bg-transparent px-5 py-4 text-base text-zinc-950 outline-none placeholder:text-zinc-400 dark:text-white dark:placeholder:text-zinc-500 sm:text-lg"
                >
                <button id="processButton" type="submit" class="primary-button m-1 shrink-0">
                    <span class="button-label">Process Link</span>
                    <span class="button-spinner hidden" aria-hidden="true"></span>
                </button>
            </form>

            <fieldset id="khdiamondTypeField" class="type-toggle mt-4 hidden" aria-label="KHDiamond video type">
                <legend class="sr-only">KHDiamond video type</legend>
                <label class="type-option">
                    <input type="radio" name="khdiamond_type" value="movie" checked>
                    <span>Movie</span>
                </label>
                <label class="type-option">
                    <input type="radio" name="khdiamond_type" value="tv">
                    <span>TV Show</span>
                </label>
            </fieldset>

            <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Supported sites: KHDiamond, KHAnime, KHFullHD</p>

            <div id="capacityBanner" class="mt-6 hidden w-full rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-left text-sm font-medium text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
                Server is currently at max capacity. Your download will be queued.
            </div>

            <section id="resultCard" class="result-card mt-8 hidden w-full text-left" aria-live="polite"></section>
        </section>

        <section id="how-it-works" class="mx-auto mt-24 max-w-6xl scroll-mt-24">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-teal-600 dark:text-teal-400">How It Works</p>
                <h2 class="mt-3 text-3xl font-bold tracking-normal text-zinc-950 dark:text-white sm:text-5xl">Three steps. No friction.</h2>
                <p class="mt-4 text-base leading-7 text-zinc-600 dark:text-zinc-400 sm:text-lg">KH Downloader is a link in, file out tool. No account, no desktop app, no browser extension. Paste your supported video link and go.</p>
            </div>

            <div class="mt-10 grid gap-5 md:grid-cols-3">
                <article class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="font-mono text-sm font-bold text-teal-600 dark:text-teal-400">01</p>
                    <div class="mt-5 grid h-14 w-14 place-items-center rounded-lg bg-teal-50 text-teal-700 dark:bg-teal-500/15 dark:text-teal-300">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M10 7H8.5a5 5 0 0 0 0 10H10M14 7h1.5a5 5 0 0 1 0 10H14M9 12h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="mt-7 text-2xl font-bold text-zinc-950 dark:text-white">Paste a link</h3>
                    <p class="mt-3 leading-7 text-zinc-600 dark:text-zinc-400">Copy a KHDiamond, KHAnime, or KHFullHD video page URL and paste it into the input box.</p>
                </article>

                <article class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="font-mono text-sm font-bold text-teal-600 dark:text-teal-400">02</p>
                    <div class="mt-5 grid h-14 w-14 place-items-center rounded-lg bg-teal-50 text-teal-700 dark:bg-teal-500/15 dark:text-teal-300">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="mt-7 text-2xl font-bold text-zinc-950 dark:text-white">Process</h3>
                    <p class="mt-3 leading-7 text-zinc-600 dark:text-zinc-400">Click process and wait a few seconds while we fetch the highest quality direct download links for you.</p>
                </article>

                <article class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="font-mono text-sm font-bold text-teal-600 dark:text-teal-400">03</p>
                    <div class="mt-5 grid h-14 w-14 place-items-center rounded-lg bg-teal-50 text-teal-700 dark:bg-teal-500/15 dark:text-teal-300">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="mt-7 text-2xl font-bold text-zinc-950 dark:text-white">Download</h3>
                    <p class="mt-3 leading-7 text-zinc-600 dark:text-zinc-400">Choose your preferred quality and start the download. Your file will be ready in an instant.</p>
                </article>
            </div>
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
@endsection
