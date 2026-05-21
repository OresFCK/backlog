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
        class="group overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 transition hover:border-zinc-700 hover:bg-zinc-800"
    >
        <img
            :src="
                game.cover_url ||
                game.capsule ||
                'https://placehold.co/600x900?text=No+Image'
            "
            class="aspect-[3/4] w-full object-cover transition duration-300 group-hover:scale-105"
        />

        <div class="space-y-3 p-4">

            <div
                class="flex items-start justify-between gap-3"
            >

                <h3
                    class="line-clamp-2 text-sm font-bold text-white"
                >
                    {{ game.title || game.name }}
                </h3>

                <StatusBadge
                    v-if="game.status"
                    :status="game.status"
                    :color="
                        game.status_color
                    "
                />

            </div>

            <p
                v-if="game.publisher"
                class="text-xs text-zinc-500"
            >
                {{ game.publisher }}
            </p>

            <div
                v-if="game.playtime_forever"
                class="text-xs font-semibold text-zinc-400"
            >
                {{
                    (
                        game.playtime_forever / 60
                    ).toFixed(1)
                }}h played
            </div>
        </div>
    </Link>
</template>