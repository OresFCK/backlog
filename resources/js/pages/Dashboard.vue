<script setup>
import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import GameGrid from '@/components/game/GameGrid.vue'
import RecommendationCard from '@/components/game/RecommendationCard.vue'

const recommended = {
    game: {
        id: 1,
        title: 'Hades',
        header_image_url:
            'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1145360/header.jpg',
    },
    reason:
        'Polecane, bo jest relatywnie krótka i ma bardzo dobre oceny.',
}

const games = [
    {
        id: 1,
        title: 'Hades',
        cover_url:
            'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1145360/library_600x900.jpg',
        status: 'backlog',
        average_playtime_minutes: 1200,
        platform: 'Steam',
        rating: 95,
    },

    {
        id: 2,
        title: 'Cyberpunk 2077',
        cover_url:
            'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1091500/library_600x900.jpg',
        status: 'playing',
        average_playtime_minutes: 2400,
        platform: 'Steam',
        rating: 86,
    },

    {
        id: 3,
        title: 'Firewatch',
        cover_url:
            'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/383870/library_600x900.jpg',
        status: 'wishlist',
        average_playtime_minutes: 300,
        platform: 'Steam',
        rating: 89,
    },

    {
        id: 4,
        title: 'Celeste',
        cover_url:
            'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/504230/library_600x900.jpg',
        status: 'finished',
        average_playtime_minutes: 600,
        platform: 'Steam',
        rating: 91,
    },
]
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar />

            <main class="flex-1 space-y-10 p-8">
                <RecommendationCard
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
                                Your backlog
                            </h2>

                            <p class="mt-1 text-zinc-400">
                                24 games waiting to be finished
                            </p>
                        </div>

                        <button
                            class="rounded-xl border border-zinc-700 bg-zinc-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-zinc-800"
                        >
                            Add game
                        </button>
                    </div>

                    <GameGrid :games="games" />
                </section>
            </main>
        </div>
    </div>
</template>