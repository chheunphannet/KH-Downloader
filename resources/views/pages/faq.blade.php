<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Answers about KH Downloader supported sites, free use, download limits, privacy, and wait list behavior.">
    <link rel="canonical" href="{{ route('pages.faq') }}">
    <title>FAQ - KH Downloader</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.webp') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-50">
    <header class="border-b border-zinc-200 bg-white/90 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/90">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ url('/') }}" class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">KH Downloader</a>
            <a href="{{ url('/') }}" class="secondary-button">Downloader</a>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-teal-600 dark:text-teal-400">Help Center</p>
        <h1 class="mt-3 text-4xl font-bold text-zinc-950 dark:text-white">Frequently Asked Questions</h1>
        <p class="mt-4 text-base leading-7 text-zinc-600 dark:text-zinc-400">Quick answers about using KH Downloader for fast, high quality video downloads from supported sources.</p>

        <section class="mt-10 space-y-7">
            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">What sites are supported?</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">KH Downloader currently supports KHDiamond, KHAnime, and KHFullHD links. Additional sources may be added after they are tested for reliable processing.</p>
            </article>

            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Is KH Downloader free?</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">Yes. The service is free to use and does not require account registration.</p>
            </article>

            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">What is the download limit?</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">The service processes a limited number of downloads at one time to keep performance stable. If capacity is full, your request may wait until an active slot is available.</p>
            </article>

            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Do you store downloaded videos?</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">No. KH Downloader processes links for the requested download flow and does not keep a permanent copy of downloaded video files.</p>
            </article>

            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Can I use downloaded content commercially?</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">You are responsible for respecting copyright, platform rules, and creator permissions. KH Downloader is intended for personal use with content you are allowed to access and download.</p>
            </article>
        </section>
    </main>

    <x-footer />
</body>
</html>
