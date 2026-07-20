<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ListOrdered, Search, Share2 } from 'lucide-vue-next';
</script>

<template>
    <section class="border-y border-zinc-800 bg-zinc-950 py-20 sm:py-24">
        <div
            class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:items-center"
        >
            <div>
                <p class="text-sm font-black text-fuchsia-300 uppercase">
                    Useful before you register
                </p>
                <h2 class="mt-3 text-3xl font-black sm:text-5xl">
                    Public gaming tools anyone can use.
                </h2>
                <p class="mt-5 leading-7 text-zinc-400">
                    Explore community game pages and build shareable tier lists
                    without connecting an account. Sign in only when you want to
                    save your work and build a permanent profile.
                </p>
                <Link
                    href="/tier-list-maker"
                    class="mt-7 inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-black text-black"
                >
                    Open Tier List Maker <ArrowRight class="h-4 w-4" />
                </Link>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <article
                    class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5"
                >
                    <Search class="h-6 w-6 text-sky-300" />
                    <h3 class="mt-4 font-black">Explore games</h3>
                    <p class="mt-2 text-sm leading-6 text-zinc-500">
                        Read summaries, ratings and public player reviews.
                    </p>
                </article>
                <article
                    class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5"
                >
                    <ListOrdered class="h-6 w-6 text-amber-300" />
                    <h3 class="mt-4 font-black">Rank your favorites</h3>
                    <p class="mt-2 text-sm leading-6 text-zinc-500">
                        Create custom game tiers with mobile-friendly controls.
                    </p>
                </article>
                <article
                    class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5"
                >
                    <Share2 class="h-6 w-6 text-emerald-300" />
                    <h3 class="mt-4 font-black">Share and remix</h3>
                    <p class="mt-2 text-sm leading-6 text-zinc-500">
                        Send a finished result or a blank template to friends.
                    </p>
                </article>
            </div>
        </div>
    </section>
</template>
