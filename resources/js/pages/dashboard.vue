<script setup>
import { computed, ref } from 'vue'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import GameGrid from '@/components/game/GameGrid.vue'
import RecommendationCard from '@/components/game/RecommendationCard.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },

    games: {
        type: Array,
        default: () => [],
    },
})

const sortBy = ref('name')
const searchQuery = ref('')

const recommended = computed(() => {
    if (!props.games.length) {
        return null
    }

    const randomGame =
        props.games[
            Math.floor(Math.random() * props.games.length)
        ]

    return {
        game: {
            id: randomGame.appid,
            title: randomGame.name,
            header_image_url: `https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/${randomGame.appid}/header.jpg`,
        },

        reason:
            'Recommended from your Steam library based on your owned games.',
    }
})

const mappedGames = computed(() => {
    let games = props.games.map((game) => ({
        id: game.appid,

        title: game.name,

        cover_url: `https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/${game.appid}/library_600x900.jpg`,

        status: 'backlog',

        average_playtime_minutes:
            game.playtime_forever ?? 0,

        platform: 'Steam',

        rating: Math.floor(Math.random() * 20) + 80,
    }))

    if (searchQuery.value.trim()) {
        games = games.filter((game) =>
            game.title
                .toLowerCase()
                .includes(
                    searchQuery.value.toLowerCase()
                )
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
                (a, b) => b.rating - a.rating
            )

        case 'name':
        default:
            return games.sort((a, b) =>
                a.title.localeCompare(b.title)
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
                    :recommendation="recommended"
                />

                <section>
                    <div
                        class="mb-6 flex items-center justify-between"
                    >
                        <div>
                            <h2
                                class="text-2xl font-bold text-white"
                            >
                                Your Steam library
                            </h2>

                            <p class="mt-1 text-zinc-400">
                                {{ mappedGames.length }}
                                games imported from Steam
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search games..."
                                class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none transition placeholder:text-zinc-500 focus:border-zinc-500"
                            />

                            <select
                                v-model="sortBy"
                                class="appearance-none rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 pr-1 text-sm text-white outline-none transition focus:border-zinc-500"
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

                            <a
                                href="https://steamcommunity.com"
                                target="_blank"
                                class="rounded-xl border border-zinc-700 bg-zinc-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-zinc-800"
                            >
                                Open Steam
                            </a>
                        </div>
                    </div>

                    <GameGrid :games="mappedGames" />

                    <div
                        v-if="!mappedGames.length"
                        class="rounded-3xl border border-zinc-800 bg-zinc-900/50 p-10 text-center"
                    >
                        <h3
                            class="text-2xl font-bold text-white"
                        >
                            No games found
                        </h3>

                        <p class="mt-3 text-zinc-400">
                            Try changing your search query or
                            sorting settings.
                        </p>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>