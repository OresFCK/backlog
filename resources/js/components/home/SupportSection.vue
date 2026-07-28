<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

defineProps({
    auth: {
        type: Object,
        default: () => ({ user: null }),
    },
});
</script>

<template>
    <section class="px-4 pb-20 sm:px-6 sm:pb-24">
        <div
            class="mx-auto max-w-6xl overflow-hidden rounded-3xl border border-indigo-500/20 bg-gradient-to-br from-indigo-500/15 via-zinc-900 to-zinc-950 p-7 text-center sm:p-12"
        >
            <p class="text-sm font-black text-indigo-300 uppercase">
                Your next game is already in your library
            </p>
            <h2 class="mx-auto mt-4 max-w-3xl text-3xl font-black sm:text-5xl">
                Turn hundreds of owned games into a collection you actually
                understand.
            </h2>
            <p class="mx-auto mt-5 max-w-2xl leading-7 text-zinc-400">
                Connect your public Steam library, organize your backlog and
                start building a gaming profile shaped by your own taste.
            </p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a
                    :href="auth.user ? '/dashboard' : '/auth/steam'"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-6 py-4 text-sm font-black text-black"
                >
                    {{
                        auth.user
                            ? 'Go back to dashboard'
                            : 'Get started with Steam'
                    }}
                    <ArrowRight class="h-4 w-4" />
                </a>
                <Link
                    href="/about"
                    class="rounded-xl border border-zinc-700 px-6 py-4 text-sm font-black hover:bg-zinc-900"
                    >Learn more about Curator.gg</Link
                >
            </div>
        </div>
    </section>
</template>
