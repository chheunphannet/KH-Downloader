@extends('layouts.app')

@section('title', 'Terms of Service - KH Downloader')
@section('meta_description', 'KH Downloader Terms of Service covering personal use, user responsibility, intellectual property, availability, and warranty limits.')

@section('content')
    <header class="border-b border-zinc-200 bg-white/90 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/90">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ url('/') }}" class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">KH Downloader</a>
            <a href="{{ url('/') }}" class="secondary-button">Downloader</a>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-teal-600 dark:text-teal-400">Legal</p>
        <h1 class="mt-3 text-4xl font-bold text-zinc-950 dark:text-white">Terms of Service</h1>
        <p class="mt-4 text-base leading-7 text-zinc-600 dark:text-zinc-400">By using KH Downloader, you agree to these terms. If you do not agree, do not use the service.</p>

        <section class="mt-10 space-y-7">
            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Personal Use</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">KH Downloader is provided for personal use only. You are responsible for making sure your use of any downloaded content is lawful and allowed by the original platform or rights holder.</p>
            </article>

            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">User Responsibility</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">You must not use the service to violate copyright, bypass access controls, distribute unauthorized copies, or download content you do not have permission to use.</p>
            </article>

            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Intellectual Property</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">Videos, audio, subtitles, artwork, and other media remain the property of their original creators, publishers, platforms, or rights holders. KH Downloader does not claim ownership of third-party content.</p>
            </article>

            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Service Availability</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">The service may limit concurrent downloads, queue requests, or become unavailable during maintenance, high traffic, or source-site changes.</p>
            </article>

            <article>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">No Warranty</h2>
                <p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-400">KH Downloader is provided as-is without warranties of availability, accuracy, fitness for a particular purpose, or uninterrupted operation.</p>
            </article>
        </section>
    </main>
@endsection
