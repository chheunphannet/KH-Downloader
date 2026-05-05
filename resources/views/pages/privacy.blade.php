<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="KH Downloader Privacy Policy explaining functional cookies, temporary processing, server logs, and video storage practices.">
    <link rel="canonical" href="{{ route('pages.privacy') }}">
    <title>Privacy Policy - KH Downloader</title>
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
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-teal-600 dark:text-teal-400">Privacy</p>
        <h1 class="mt-3 text-4xl font-bold text-zinc-950 dark:text-white">Privacy Policy</h1>
        <p class="mt-4 text-base leading-7 text-zinc-600 dark:text-zinc-400">This policy explains what KH Downloader collects and how data is used to provide the service.</p>

        <section class="mt-10 space-y-7">
            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Video Storage</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">KH Downloader does not store videos permanently after processing. Temporary files or streams may exist only as needed to complete a requested download.</p>
            </article>

            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Functional Cookies</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">The service may use cookies or session identifiers strictly for functionality, such as keeping requests stable and managing active download capacity.</p>
            </article>

            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Server Logs</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">Basic technical data such as IP address, request time, user agent, and requested URL may be logged for security, abuse prevention, performance monitoring, and the concurrent-use limit.</p>
            </article>

            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Analytics</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">If analytics are enabled, they should be used to understand aggregate usage and improve reliability. Do not submit private or sensitive links through the service.</p>
            </article>

            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Third-Party Content</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">Downloaded content comes from third-party websites. Those websites may have their own privacy policies, logs, cookies, and terms that apply when you visit or use them.</p>
            </article>
        </section>
    </main>

    <x-footer />
</body>
</html>
