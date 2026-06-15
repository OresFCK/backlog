<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Curator.gg – Organize Your Steam Library</title>

    <meta
        name="description"
        content="Connect your Steam account, organize your game library, discover what to play next, track your backlog and build your gaming profile."
    >

    <meta name="robots" content="index, follow">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Curator.gg">
    <meta property="og:title" content="Curator.gg – Organize Your Steam Library">
    <meta
        property="og:description"
        content="Connect your Steam account, organize your game library, discover what to play next and build your gaming profile."
    >

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Curator.gg – Organize Your Steam Library">
    <meta
        name="twitter:description"
        content="Connect your Steam account, organize your game library, discover what to play next and build your gaming profile."
    >

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

    <link rel="icon" href="/favicon.png">
    <link rel="apple-touch-icon" href="/favicon.png">

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    <x-inertia::head />
</head>

<body class="font-sans antialiased">
    <x-inertia::app />
</body>
</html>