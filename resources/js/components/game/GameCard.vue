<script setup>
import { Link } from '@inertiajs/vue3'

import StatusBadge from '@/components/game/StatusBadge.vue'

defineProps({
    game: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <Link
        :href="`/games/${game.id ?? game.appid}`"
        class="group flex h-full flex-col overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 transition hover:border-zinc-700 hover:bg-zinc-800"
    >
        <div class="aspect-[16/9] overflow-hidden bg-zinc-950">
            <img
                :src="
                    game.cover_url ||
                    game.header_image ||
                    game.capsule ||
                    'https://placehold.co/600x338?text=No+Image'
                "
                :alt="game.title || game.name"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
            />
        </div>

        <div class="flex flex-1 items-center justify-between gap-3 p-4">
            <h3 class="line-clamp-1 text-sm font-bold text-white">
                {{ game.title || game.name }}
            </h3>

            <StatusBadge
                v-if="game.status"
                :status="game.status"
                :color="game.status_color"
            />
        </div>
    </Link>
</template>