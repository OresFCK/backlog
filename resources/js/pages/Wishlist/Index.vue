<script setup>
import Sidebar from '@/components/layout/sidebar.vue'
import Topbar from '@/components/layout/topbar.vue'

import GameGrid from '@/components/game/gameGrid.vue'

const games = [
    {
        id: 1,
        title: 'Black Myth: Wukong',
        cover_url:
            'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2358720/library_600x900.jpg',
        status: 'wishlist',
        average_playtime_minutes: 1800,
        platform: 'Steam',
        rating: 93,
    },

    {
        id: 2,
        title: 'Silksong',
        cover_url:
            'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1030300/library_600x900.jpg',
        status: 'wishlist',
        average_playtime_minutes: null,
        platform: 'Steam',
        rating: 'TBA',
    },
]
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar />

            <main class="flex-1 p-8">
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-white">
                        Wishlist
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Games you want to buy or play later.
                    </p>
                </div>

                <GameGrid :games="games" />
            </main>
        </div>
    </div>
</template>