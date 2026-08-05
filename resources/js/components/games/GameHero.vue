<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    game: {
        type: Object,
        required: true,
    },
});

defineEmits(['create-review']);
</script>

<template>
    <div
        class="relative bg-cover bg-center"
        :style="{
            backgroundImage: game.header_image
                ? `linear-gradient(to right, rgba(9,9,11,.95), rgba(9,9,11,.45)), url(${game.header_image})`
                : null,
        }"
    >
        <div
            class="grid gap-6 p-5 sm:min-h-[360px] sm:p-8 lg:grid-cols-[minmax(0,1fr)_280px] lg:gap-8 lg:p-10 xl:grid-cols-[minmax(0,1fr)_320px]"
        >
            <div class="flex flex-col justify-between">
                <div>
                    <div class="mb-3 flex flex-wrap gap-2 sm:mb-4">
                        <span
                            v-for="genre in game.genres"
                            :key="genre"
                            class="rounded-full border border-zinc-700 bg-zinc-950/70 px-2.5 py-1 text-[11px] font-semibold text-zinc-300 sm:px-3 sm:text-xs"
                        >
                            {{ genre }}
                        </span>
                    </div>

                    <h1
                        class="max-w-3xl text-3xl leading-tight font-black break-words text-white sm:text-4xl lg:text-5xl"
                    >
                        {{ game.title }}
                    </h1>

                    <p
                        class="mt-3 max-w-2xl text-sm leading-6 text-zinc-300 sm:mt-4 sm:text-base"
                    >
                        {{ game.description || 'No description available.' }}
                    </p>
                </div>

                <div
                    class="mt-6 grid gap-2.5 sm:mt-8 sm:flex sm:flex-wrap sm:gap-3"
                >
                    <button
                        type="button"
                        class="w-full rounded-xl bg-white px-4 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200 sm:w-auto sm:px-5"
                        @click="$emit('create-review')"
                    >
                        Create public review
                    </button>

                    <Link
                        v-if="game.slug"
                        :href="`/${game.slug}`"
                        class="w-full rounded-xl border border-zinc-700 bg-zinc-950/80 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-zinc-900 sm:w-auto sm:px-5"
                    >
                        Community Hub
                    </Link>

                    <a
                        v-if="game.igdb_url"
                        :href="game.igdb_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="w-full rounded-xl border border-zinc-700 bg-zinc-950/80 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-zinc-900 sm:w-auto sm:px-5"
                    >
                        Check on IGDB
                    </a>

                    <a
                        v-if="game.steam_url"
                        :href="game.steam_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="w-full rounded-xl border border-zinc-700 bg-zinc-950/80 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-zinc-900 sm:w-auto sm:px-5"
                    >
                        Open on Steam
                    </a>
                </div>
            </div>

            <Link
                v-if="game.suggested_game"
                :href="game.suggested_game.url"
                class="group w-full overflow-hidden rounded-2xl border border-zinc-700 bg-zinc-950/90 shadow-2xl backdrop-blur transition hover:border-zinc-500 hover:bg-zinc-900/90 sm:max-w-sm sm:rounded-3xl lg:w-64 lg:self-end"
            >
                <img
                    v-if="game.suggested_game.cover_url"
                    :src="game.suggested_game.cover_url"
                    :alt="game.suggested_game.title"
                    class="h-32 w-full object-cover sm:h-36"
                />

                <div class="p-3 sm:p-4">
                    <p
                        class="text-xs font-bold tracking-[0.25em] text-zinc-500 uppercase"
                    >
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

                    <p class="mt-4 text-sm font-bold text-white">View game →</p>
                </div>
            </Link>
        </div>
    </div>
</template>
