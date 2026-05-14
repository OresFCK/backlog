<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    game: {
        type: Object,
        required: true,
    },
})

const statusColors = {
    backlog:
        'bg-zinc-700/70 text-zinc-200 border border-zinc-600',

    playing:
        'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30',

    finished:
        'bg-blue-500/20 text-blue-300 border border-blue-500/30',

    wishlist:
        'bg-pink-500/20 text-pink-300 border border-pink-500/30',
}
</script>

<template>
    <Link
        :href="`/games/${game.id}`"
        class="group block overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900/70 transition-all hover:-translate-y-1 hover:border-zinc-700 hover:bg-zinc-900"
    >
        <div class="relative aspect-[3/4] overflow-hidden">
            <img
                :src="
                    game.cover_url ||
                    'https://placehold.co/600x900/18181b/ffffff?text=No+Cover'
                "
                :alt="game.title"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            />

            <div
                class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"
            />

            <div class="absolute left-4 top-4">
                <span
                    class="rounded-full px-3 py-1 text-xs font-semibold backdrop-blur-sm"
                    :class="
                        statusColors[game.status] ||
                        statusColors.backlog
                    "
                >
                    {{ game.status }}
                </span>
            </div>
        </div>

        <div class="space-y-3 p-5">
            <div>
                <h3
                    class="line-clamp-1 text-lg font-bold text-white"
                >
                    {{ game.title }}
                </h3>

                <p class="mt-1 text-sm text-zinc-400">
                    <template
                        v-if="
                            game.average_playtime_minutes
                        "
                    >
                        {{
                            Math.round(
                                game.average_playtime_minutes /
                                    60
                            )
                        }}h played
                    </template>

                    <template v-else>
                        Unknown length
                    </template>
                </p>
            </div>

            <div
                class="flex items-center justify-between"
            >
                <span
                    class="text-sm font-medium text-zinc-500"
                >
                    {{ game.platform || 'Custom' }}
                </span>

                <div
                    class="rounded-lg bg-zinc-800 px-2 py-1 text-sm font-bold text-zinc-200"
                >
                    {{ game.rating || '--' }}
                </div>
            </div>
        </div>
    </Link>
</template>