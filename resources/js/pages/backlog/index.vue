<script setup>
import { computed, ref } from 'vue'

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

const sortBy = ref('name')
const searchQuery = ref('')
const selectedStatus = ref('all')

const mappedGames = computed(() => {
    let games = props.games
        .filter((game) => game)
        .map((game) => {
            const gameId = game.id ?? game.appid

            const isCustom =
                game.is_custom === true ||
                game.source === 'manual' ||
                game.source === 'igdb'

            const status =
                props.statuses.find(
                    (item) =>
                        String(item?.name ?? '').toLowerCase().trim() ===
                        String(game?.status ?? '').toLowerCase().trim()
                ) ?? null

            return {
                id: gameId,
                appid: game.appid ?? null,
                is_custom: isCustom,

                title:
                    game.title ??
                    game.name ??
                    'Unknown game',

                cover_url:
                    game.cover_url ??
                    game.header_image_url ??
                    null,

                status:
                    status?.name ??
                    game.status ??
                    'Backlog',

                status_color:
                    status?.color ??
                    game.status_color ??
                    '#71717a',

                average_playtime_minutes:
                    Number(game.playtime_forever ?? 0),

                platform:
                    isCustom
                        ? 'Custom'
                        : 'Steam',

                rating:
                    game.rating ?? null,
            }
        })

    if (searchQuery.value.trim()) {
        games = games.filter((game) =>
            String(game.title)
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase())
        )
    }

    if (selectedStatus.value !== 'all') {
        games = games.filter(
            (game) => game.status === selectedStatus.value
        )
    }

    switch (sortBy.value) {
        case 'playtime':
            return [...games].sort(
                (a, b) =>
                    b.average_playtime_minutes -
                    a.average_playtime_minutes
            )

        case 'rating':
            return [...games].sort(
                (a, b) =>
                    (b.rating ?? 0) - (a.rating ?? 0)
            )

        default:
            return [...games].sort((a, b) =>
                String(a.title).localeCompare(String(b.title))
            )
    }
})
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 p-8">
                <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 class="text-4xl font-bold text-white">
                            Backlog
                        </h1>
                        <p class="mt-2 text-zinc-400"> Organize and finish your games. </p>

                        <p class="mt-2 text-zinc-400 text-sm">
                            {{ mappedGames.length }} games available
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search games..."
                            class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-500"
                        />

                        <select
                            v-model="sortBy"
                            class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        >
                            <option value="name">
                                Sort by name
                            </option>

                            <option value="playtime">
                                Sort by playtime
                            </option>

                            <option value="rating">
                                Sort by rating
                            </option>
                        </select>
                    </div>
                </div>

                <GameGrid :games="mappedGames" />
            </main>
        </div>
    </div>
</template>