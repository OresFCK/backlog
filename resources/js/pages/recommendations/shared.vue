<!-- eslint-disable vue/block-lang -->
<script setup>
import { Link } from '@inertiajs/vue3';
import { Gamepad2, Sparkles } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps({
    games: { type: Array, required: true },
    mood: { type: String, default: null },
});

const failedImages = ref(new Set());
const moodLabels = {
    short: 'Short and focused',
    immersive: 'Something immersive',
    chill: 'Chill',
    friends: 'With friends',
    surprise: 'Surprise me',
};

const imageFailed = (game) => {
    if (
        game.image_fallback_url &&
        game.cover_image_url !== game.image_fallback_url
    ) {
        failedImages.value = new Set([...failedImages.value, game.id]);
    }
};
</script>

<template>
    <main class="min-h-screen bg-zinc-950 px-4 py-10 text-white sm:py-16">
        <section class="mx-auto max-w-5xl">
            <div class="text-center">
                <div
                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/15 text-indigo-300"
                >
                    <Sparkles class="h-6 w-6" />
                </div>
                <p
                    class="mt-5 text-xs font-bold tracking-[0.25em] text-indigo-400 uppercase"
                >
                    Shared from Curator.gg
                </p>
                <h1 class="mt-3 text-4xl font-black sm:text-6xl">
                    My next three games
                </h1>
                <p v-if="mood" class="mt-3 text-zinc-400">
                    Mood:
                    <span class="font-bold text-zinc-200">{{
                        moodLabels[mood]
                    }}</span>
                </p>
            </div>

            <div class="mt-10 grid gap-5 sm:grid-cols-3">
                <a
                    v-for="(game, index) in games"
                    :key="game.id"
                    :href="game.url"
                    class="group overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900 transition hover:-translate-y-1 hover:border-indigo-500/60"
                >
                    <div
                        class="relative aspect-[2/3] overflow-hidden bg-zinc-800"
                    >
                        <img
                            :src="
                                failedImages.has(game.id)
                                    ? game.image_fallback_url
                                    : game.cover_image_url
                            "
                            :alt="game.title"
                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            @error="imageFailed(game)"
                        />
                        <span
                            class="absolute top-4 left-4 rounded-full bg-black/80 px-3 py-1 text-sm font-black"
                            >#{{ index + 1 }}</span
                        >
                    </div>
                    <div class="p-5">
                        <p
                            class="text-xs font-bold tracking-widest text-indigo-400 uppercase"
                        >
                            {{ index === 0 ? 'Best match' : 'Strong match' }}
                        </p>
                        <h2 class="mt-2 text-xl font-black">
                            {{ game.title }}
                        </h2>
                    </div>
                </a>
            </div>

            <div class="mt-10 text-center">
                <Link
                    href="/recommendations?onboarding=start"
                    class="inline-flex items-center gap-2 rounded-2xl bg-white px-6 py-4 font-black text-zinc-950 hover:bg-zinc-200"
                >
                    <Gamepad2 class="h-5 w-5" /> Get my recommendations
                </Link>
            </div>
        </section>
    </main>
</template>
