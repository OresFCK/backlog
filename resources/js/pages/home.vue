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

                <a
                    href="/wip"
                    class="rounded-2xl border border-zinc-800 px-6 py-3 text-sm font-semibold text-white transition hover:border-zinc-700 hover:bg-zinc-900"
                >
                    Open app
                </a>
            </div>
        </div>
    </div>
</template>