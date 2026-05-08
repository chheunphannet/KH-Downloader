@extends('layouts.app')

@section('title', 'FAQ - KH Downloader')
@section('meta_description', 'Answers about KH Downloader supported sites, free use, download limits, privacy, and wait list behavior.')

@section('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "FAQPage",
  "mainEntity": [
    {
      "@@type": "Question",
      "name": "What sites are supported?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "KH Downloader currently supports KHDiamond, KHAnime, and KHFullHD links. Additional sources may be added after they are tested for reliable processing."
      }
    },
    {
      "@@type": "Question",
      "name": "Is KH Downloader free?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "Yes. The service is free to use and does not require account registration."
      }
    },
    {
      "@@type": "Question",
      "name": "What is the download limit?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "The service processes a limited number of downloads at one time to keep performance stable. If capacity is full, your request may wait until an active slot is available."
      }
    },
    {
      "@@type": "Question",
      "name": "Do you store downloaded videos?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "No. KH Downloader processes links for the requested download flow and does not keep a permanent copy of downloaded video files."
      }
    }
  ]
}
</script>
@endsection

@section('content')
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
@endsection
