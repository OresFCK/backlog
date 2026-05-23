<script setup>
import { computed, ref } from 'vue'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import GameGrid from '@/components/game/GameGrid.vue'
import RecommendationCard from '@/components/game/RecommendationCard.vue'

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

const recommended = computed(() => {
    if (!props.games.length) {
        return null
    }

    const randomGame =
        props.games[
            Math.floor(
                Math.random() *
                    props.games.length
            )
        ]

    return {
        game: {
            id: randomGame.id,

            title:
                randomGame.name ??
                randomGame.title ??
                'Unknown game',

            header_image_url:
                randomGame.cover_url ??
                `https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/${randomGame.appid}/header.jpg`,
        },

        reason:
            'Recommended from your library.',
    }
})

const mappedGames = computed(() => {
    let games = props.games.map((game) => {

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

            cover_url:
                game.cover_url ??
                `https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/${game.appid}/library_600x900.jpg`,

            status:
                status?.name ??
                'Backlog',

            status_color:
                status?.color ??
                '#71717a',

            average_playtime_minutes:
                game.playtime_forever ?? 0,

            platform: game.is_custom
                ? 'Custom'
                : 'Steam',

            rating: game.rating ?? null,
        }
    })

    if (searchQuery.value.trim()) {
        games = games.filter((game) =>
            String(game.title)
                .toLowerCase()
                .includes(
                    searchQuery.value.toLowerCase()
                )
        )
    }

    if (
        selectedStatus.value !== 'all'
    ) {
        games = games.filter(
            (game) =>
                game.status ===
                selectedStatus.value
        )
    }

    switch (sortBy.value) {
        case 'playtime':
            return games.sort(
                (a, b) =>
                    b.average_playtime_minutes -
                    a.average_playtime_minutes
            )

        case 'rating':
            return games.sort(
                (a, b) =>
                    (b.rating ?? 0) -
                    (a.rating ?? 0)
            )

        default:
            return games.sort((a, b) =>
                String(a.title).localeCompare(
                    String(b.title)
                )
            )
    }
})
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 space-y-10 p-8">
                <RecommendationCard
                    v-if="recommended"
                    :recommendation="
                        recommended
                    "
                />

                <section>
                    <div
                        class="mb-6 flex flex-wrap items-center justify-between gap-4"
                    >
                        <div>
                            <h2
                                class="text-2xl font-bold text-white"
                            >
                                Your game library
                            </h2>

                            <p
                                class="mt-1 text-zinc-400"
                            >
                                {{
                                    mappedGames.length
                                }}
                                games available
                            </p>
                        </div>

                        <div
                            class="flex flex-wrap items-center gap-4"
                        >
                            <input
                                v-model="
                                    searchQuery
                                "
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

                            <select
                                v-model="
                                    selectedStatus
                                "
                                class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                            >
                                <option value="all">
                                    All statuses
                                </option>

                                <option
                                    v-for="status in statuses"
                                    :key="
                                        status.id
                                    "
                                    :value="
                                        status.name
                                    "
                                >
                                    {{
                                        status.name
                                    }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <GameGrid
                        :games="mappedGames"
                    />
                </section>
            </main>
        </div>
    </div>
</template>