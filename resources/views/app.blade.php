@php
    $seo = $page['props']['seo'] ?? [];
    $siteName = config('app.name', 'Yemi Adebanjo');
    $title = !empty($seo['title']) ? $seo['title'].' — '.$siteName : $siteName.' — Author, Leadership Teacher & Consultant';
    $description = $seo['description'] ?? 'Official website of Yemi Adebanjo: books, consultations, teachings, events and speaking engagements.';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0b2c1d">

        <title inertia>{{ $title }}</title>
        <meta name="description" content="{{ $description }}">
        @if (!empty($seo['canonical']))
            <link rel="canonical" href="{{ $seo['canonical'] }}">
        @endif
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:type" content="{{ $seo['type'] ?? 'website' }}">
        <meta property="og:image" content="{{ $seo['image'] ?? asset('images/brand/logo-original.jpg') }}">
        @if (!empty($seo['canonical']))
            <meta property="og:url" content="{{ $seo['canonical'] }}">
        @endif
        <meta name="twitter:card" content="summary_large_image">
        @if (!empty($seo['json_ld']))
            <script type="application/ld+json">{!! json_encode($seo['json_ld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
        @endif

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|cormorant-garamond:500,600,700,500i&display=swap" rel="stylesheet" />

        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
