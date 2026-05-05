<footer class="border-t border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 text-sm text-zinc-600 dark:text-zinc-400 sm:px-6 md:grid-cols-4">
        <div>
            <a href="{{ url('/') }}" class="text-base font-semibold text-zinc-950 dark:text-white">KH Downloader</a>
            <p class="mt-3 leading-6">Fast video link processing for supported KH streaming sites. No registration required.</p>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-zinc-950 dark:text-white">Support</h2>
            <nav class="mt-3 flex flex-col gap-2" aria-label="Support links">
                <a href="{{ route('pages.faq') }}" class="transition hover:text-zinc-950 dark:hover:text-white">FAQ</a>
                <a href="{{ url('/#downloaderApp') }}" class="transition hover:text-zinc-950 dark:hover:text-white">Start Download</a>
                <a href="{{ route('admin.metrics') }}" class="transition hover:text-zinc-950 dark:hover:text-white">Admin Metrics</a>
            </nav>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-zinc-950 dark:text-white">Legal</h2>
            <nav class="mt-3 flex flex-col gap-2" aria-label="Legal links">
                <a href="{{ route('pages.terms') }}" class="transition hover:text-zinc-950 dark:hover:text-white">Terms of Service</a>
                <a href="{{ route('pages.privacy') }}" class="transition hover:text-zinc-950 dark:hover:text-white">Privacy Policy</a>
            </nav>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-zinc-950 dark:text-white">Supported Sites</h2>
            <p class="mt-3 leading-6">KHDiamond, KHAnime, and KHFullHD. More sources may be added as the service expands.</p>
        </div>
    </div>
</footer>
