<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    game: {
        type: Object,
        required: true,
    },
})

defineEmits(['create-review'])
</script>

<template>
    <div
        class="relative min-h-[360px] bg-cover bg-center"
        :style="{
            backgroundImage: game.header_image
                ? `linear-gradient(to right, rgba(9,9,11,.95), rgba(9,9,11,.55)), url(${game.header_image})`
                : null,
        }"
    >
        <div class="grid min-h-[360px] gap-8 p-10 lg:grid-cols-[1fr_320px]">
            <div class="flex flex-col justify-between">
                <div>
                    <div class="mb-4 flex flex-wrap gap-2">
                        <span
                            v-for="genre in game.genres"
                            :key="genre"
                            class="rounded-full border border-zinc-700 bg-zinc-950/70 px-3 py-1 text-xs font-semibold text-zinc-300"
                        >
                            {{ genre }}
                        </span>
                    </div>

                    <h1 class="max-w-3xl text-5xl font-black text-white">
                        {{ game.title }}
                    </h1>

                    <p class="mt-4 max-w-2xl text-zinc-300">
                        {{ game.description || 'No description available.' }}
                    </p>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <button
                        type="button"
                        class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                        @click="$emit('create-review')"
                    >
                        Create public review
                    </button>

                    <Link
                        v-if="game.slug"
                        :href="`/${game.slug}`"
                        class="rounded-xl border border-zinc-700 bg-zinc-950/80 px-5 py-3 text-sm font-bold text-white transition hover:bg-zinc-900"
                    >
                        Community Hub
                    </Link>

                    <a
                        v-if="game.igdb_url"
                        :href="game.igdb_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="rounded-xl border border-zinc-700 bg-zinc-950/80 px-5 py-3 text-sm font-bold text-white transition hover:bg-zinc-900"
                    >
                        Check on IGDB
                    </a>

                    <a
                        v-if="game.steam_url"
                        :href="game.steam_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="rounded-xl border border-zinc-700 bg-zinc-950/80 px-5 py-3 text-sm font-bold text-white transition hover:bg-zinc-900"
                    >
                        Open on Steam
                    </a>
                </div>
            </div>

            <Link
                v-if="game.suggested_game"
                :href="game.suggested_game.url"
                class="group w-64 self-end overflow-hidden rounded-3xl border border-zinc-700 bg-zinc-950/80 shadow-2xl backdrop-blur transition hover:border-zinc-500 hover:bg-zinc-900/90"
            >
                <img
                    v-if="game.suggested_game.cover_url"
                    :src="game.suggested_game.cover_url"
                    :alt="game.suggested_game.title"
                    class="h-36 w-full object-cover"
                />

                <div class="p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-zinc-500">
                        Try something similar
                    </p>

                    <h3 class="mt-2 text-lg font-black text-white">
                        {{ game.suggested_game.title }}
                    </h3>

                    <p
                        v-if="game.suggested_game.matched_genre"
                        class="mt-2 text-sm text-zinc-400"
                    >
                        Because you are viewing
                        {{ game.suggested_game.matched_genre }}
                    </p>

                    <p class="mt-4 text-sm font-bold text-white">
                        View game →
                    </p>
                </div>
            </Link>
        </div>
    </div>
</template>