<script setup>
import StatusBadge from './statusBadge.vue'
//xd
const props = defineProps({
    game: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <div
        class="group overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 transition-all hover:scale-[1.02] hover:border-zinc-700"
    >
        <div class="relative aspect-[3/4] overflow-hidden">
            <img
                :src="game.cover_url"
                :alt="game.title"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />

            <div
                class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"
            />

            <div class="absolute bottom-3 left-3">
                <StatusBadge :status="game.status" />
            </div>
        </div>

        <div class="space-y-3 p-4">
            <div>
                <h3 class="line-clamp-1 text-sm font-semibold text-white">
                    {{ game.title }}
                </h3>

                <p class="mt-1 text-xs text-zinc-400">
                    {{ game.average_playtime_minutes
                    ? Math.round(game.average_playtime_minutes / 60) + 'h'
                    : 'Unknown length'
                    }}
                </p>
            </div>

            <div class="flex items-center justify-between">
                <div class="text-xs text-zinc-500">
                    {{ game.platform ?? 'Steam' }}
                </div>

                <div
                    class="rounded-lg bg-zinc-800 px-2 py-1 text-xs font-medium text-zinc-300"
                >
                    {{ game.rating ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>
</template>