<script setup>
import { ArrowRight } from 'lucide-vue-next';
</script>

<template>
    <section class="border-b border-zinc-800">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:py-24">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold text-indigo-400">
                    Your Steam library, organized
                </p>

                <h1
                    class="mt-4 text-4xl leading-tight font-bold tracking-tight sm:text-5xl lg:text-6xl"
                >
                    Know what you own.<br />Decide what to play next.
                </h1>

                <p
                    class="mt-5 max-w-2xl text-base leading-7 text-zinc-400 sm:text-lg"
                >
                    Keep your backlog tidy, track what you are playing and find
                    your next game with recommendations from the community.
                </p>

                <div
                    class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center"
                >
                    <a
                        href="/auth/steam"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-black transition hover:bg-zinc-200"
                    >
                        Continue with Steam
                        <ArrowRight class="h-4 w-4" />
                    </a>
                    <a
                        href="#how-it-works"
                        class="px-5 py-3 text-center text-sm font-semibold text-zinc-300 transition hover:text-white"
                    >
                        See how it works
                    </a>
                </div>

                <p class="mt-4 text-xs text-zinc-600">
                    Sign in securely through Steam. We never receive your Steam
                    password.
                </p>
            </div>

            <div
                class="mt-12 overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900 sm:mt-16"
            >
                <img
                    src="/dashboard.png"
                    alt="Curator.gg dashboard showing an organized Steam game library and recommendations"
                    class="w-full"
                />
            </div>
        </div>
    </section>
</template>
