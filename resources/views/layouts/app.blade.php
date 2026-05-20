<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#14b8a6"> {{-- Teal-500 --}}

    {{-- SEO Tags --}}
    <title>@yield('title', 'KH Downloader - Fast High Quality Video Downloader')</title>
    <meta name="description" content="@yield('meta_description', 'Fast video downloader & free player for KHDiamond, KHAnime, and KHFullHD. Watch for free or download in high quality with no registration required.')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="msvalidate.01" content="D56C8E1B28E0A11255A38EBC06B6539A" />

    {{-- Social Media (Open Graph) --}}
    <meta property="og:site_name" content="KH Downloader">
    <meta property="og:title" content="@yield('title', 'KH Downloader - Fast High Quality Video Downloader')">
    <meta property="og:description" content="@yield('meta_description', 'Fast video downloader & free player for KHDiamond, KHAnime, and KHFullHD. Watch for free or download in high quality with no registration required.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="@yield('og_image', asset('images/logo.webp'))">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'KH Downloader - Fast High Quality Video Downloader')">
    <meta name="twitter:description" content="@yield('meta_description', 'Fast video downloader & free player for KHDiamond, KHAnime, and KHFullHD. Watch for free or download in high quality with no registration required.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/logo.webp'))">

    {{-- Favicons --}}
    <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.webp') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Structured Data --}}
    @yield('structured_data')
</head>
<body data-page="@yield('page_name', 'other')" data-site="@yield('page_site', '')" data-slug="@yield('page_slug', '')" class="min-h-screen @yield('page_body_class', 'bg-zinc-50 text-zinc-950') antialiased dark:bg-zinc-950 dark:text-zinc-50">
    @yield('content')

    <x-footer />
</body>
</html>
