<template>
    <section class="mx-auto max-w-7xl px-6 py-24">
        <div class="flex flex-col items-center text-center">
            <h1 class="max-w-4xl text-5xl font-bold tracking-tight md:text-7xl">
                So many games,
                <br>
                so little time...
            </h1>

            <p class="mt-6 max-w-2xl text-lg leading-8 text-zinc-400">
                Connect your Steam account, organize your library,
                discover what to play next and build your gaming profile.
            </p>

            <a
                href="/auth/steam"
                class="mt-10 rounded-2xl bg-white px-8 py-4 text-sm font-semibold text-black transition hover:bg-zinc-200"
            >
                Login with Steam
            </a>

            <p class="mt-6 max-w-xl text-sm text-zinc-500">
                By signing in with Steam, you agree to our
                <a href="/terms" class="underline transition hover:text-zinc-300">
                    Terms of Service
                </a>
                and
                <a href="/privacy" class="underline transition hover:text-zinc-300">
                    Privacy Policy
                </a>.
            </p>
        </div>

        <div class="mt-20 overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900 shadow-2xl">
            <img
                src="/dashboard.png"
                alt="Dashboard"
                class="w-full"
            >
        </div>
    </section>
</template>