<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    games: {
        type: Array,
        default: () => [],
    },

    title: {
        type: String,
        default: 'Related Games',
    },
})

const gameUrl = (game) => {
    return `/${game.slug}`
}
</script>

<template>
    <section
        v-if="games.length"
        class="overflow-hidden"
    >
        <div class="mb-6 flex items-center justify-between border-b border-zinc-800 pb-3">
            <h2 class="text-2xl font-black">
                {{ title }}
            </h2>
        </div>

        <div class="related-games-scroll flex max-w-full gap-4 overflow-x-auto pb-4">
            <Link
                v-for="game in games"
                :key="game.id"
                :href="gameUrl(game)"
                class="block w-36 shrink-0 sm:w-40"
            >
                <img
                    v-if="game.cover_url"
                    :src="game.cover_url"
                    :alt="game.title"
                    class="aspect-[3/4] w-full rounded-lg object-cover"
                    loading="lazy"
                >

                <div
                    v-else
                    class="aspect-[3/4] w-full rounded-lg bg-zinc-800"
                />

                <h3 class="mt-3 truncate text-sm font-bold text-white underline">
                    {{ game.title }}
                </h3>

                <div
                    v-if="game.genres?.length"
                    class="mt-2 truncate text-xs text-zinc-500"
                >
                    {{ game.genres.slice(0, 2).join(' · ') }}
                </div>
            </Link>
        </div>
    </section>
</template>

<style scoped>
.related-games-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgb(82 82 91) transparent;
}

.related-games-scroll::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

.related-games-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.related-games-scroll::-webkit-scrollbar-thumb {
    background: rgb(63 63 70);
    border-radius: 999px;
    border: 3px solid rgb(9 9 11);
}

.related-games-scroll::-webkit-scrollbar-thumb:hover {
    background: rgb(113 113 122);
}

.related-games-scroll::-webkit-resizer {
    background: rgb(9 9 11);
}
</style>