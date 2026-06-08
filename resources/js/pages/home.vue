<template>
    <div class="min-h-screen bg-zinc-950 text-white">
        <div class="mx-auto flex min-h-screen max-w-5xl flex-col items-center justify-center px-6 text-center">
            <h1 class="text-6xl font-bold tracking-tight">
                backlog
            </h1>

            <p class="mt-6 max-w-2xl text-lg text-zinc-400">
                Connect your Steam account and discover what to play from your existing library.
            </p>

            <div class="mt-10 flex items-center gap-4">
                <a
                    href="/auth/steam"
                    class="rounded-2xl bg-white px-6 py-3 text-sm font-semibold text-black transition hover:bg-zinc-200"
                >
                    Login with Steam
                </a>
            </div>

            <div class="mt-8 max-w-lg text-sm text-zinc-500">
                By signing in with Steam, you agree to our
                <a
                    href="/terms"
                    class="underline transition hover:text-zinc-300"
                >
                    Terms of Service
                </a>
                and
                <a
                    href="/privacy"
                    class="underline transition hover:text-zinc-300"
                >
                    Privacy Policy
                </a>.
                Curator.gg is currently operating as a closed beta project.
            </div>
        </div>
    </div>
</template>