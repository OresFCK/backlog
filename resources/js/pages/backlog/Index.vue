<script setup>
import { computed } from 'vue'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import GameGrid from '@/components/game/GameGrid.vue'

const props = defineProps({
    user: Object,

    games: {
        type: Array,
        default: () => [],
    },

    statuses: {
        type: Array,
        default: () => [],
    },
})

const games = computed(() =>
    props.games.map((game) => {

        const status =
            props.statuses.find(
                (item) =>
                    item.name?.toLowerCase() ===
                    game.status?.toLowerCase()
            ) ?? null

        return {
            id: game.id,

            title:
                game.name ??
                game.title ??
                'Unknown game',

            cover_url: game.cover_url,

            status:
                status?.name ??
                game.status ??
                'Backlog',

            status_color:
                status?.color ??
                game.status_color ??
                '#71717a',

            average_playtime_minutes:
                game.playtime_forever ?? 0,

            platform: game.is_custom
                ? 'Custom'
                : 'Steam',

            rating: game.rating ?? null,
        }
    })
)
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 p-8">
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-white">
                        Backlog
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Organize and finish your games.
                    </p>
                </div>

                <GameGrid :games="games" />
            </main>
        </div>
    </div>
</template>