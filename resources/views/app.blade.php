<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Curator.gg – Organize Your Steam Library</title>

    <meta name="description" content="Connect your Steam account, organize your game library, discover what to play next, track your backlog and build your gaming profile.">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#09090b">

    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Curator.gg">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Curator.gg – Organize Your Steam Library">
    <meta property="og:description" content="Connect your Steam account, organize your game library, discover what to play next, track your backlog and build your gaming profile.">
    <meta property="og:image" content="{{ asset('og-image.jpg') }}">
    <meta property="og:image:secure_url" content="{{ asset('og-image.jpg') }}">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Curator.gg logo">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Curator.gg – Organize Your Steam Library">
    <meta name="twitter:description" content="Connect your Steam account, organize your game library, discover what to play next, track your backlog and build your gaming profile.">
    <meta name="twitter:image" content="{{ asset('og-image.jpg') }}">
    <meta name="twitter:image:alt" content="Curator.gg logo">

    <link rel="icon" href="/favicon.png">
    <link rel="apple-touch-icon" href="/favicon.png">

    <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "WebSite",
            "name": "Curator.gg",
            "url": "{{ url('/') }}",
            "description": "Connect your Steam account, organize your game library, discover what to play next, track your backlog and build your gaming profile."
        }
    </script>

    <script>
        (function () {
            const appearance = 'system';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia(
                    '(prefers-color-scheme: dark)'
                ).matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>

    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    <script
        async
        src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6138711947795604"
        crossorigin="anonymous">
    </script>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    <x-inertia::head />
</head>

<body class="font-sans antialiased">
    <x-inertia::app />
</body>
</html>