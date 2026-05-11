<script setup>
import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import GameGrid from '@/components/game/GameGrid.vue'

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
        title: 'Celeste',
        cover_url:
            'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/504230/library_600x900.jpg',
        status: 'playing',
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

            <main class="flex-1 p-8">
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-white">
                        Backlog
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Organize and finish your games.
                    </p>
                </div>

                <div
                    class="mb-8 flex flex-wrap items-center gap-3"
                >
                    <button
                        class="rounded-xl bg-zinc-800 px-4 py-2 text-sm font-medium text-white"
                    >
                        All
                    </button>

                    <button
                        class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-2 text-sm font-medium text-zinc-300"
                    >
                        Backlog
                    </button>

                    <button
                        class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-2 text-sm font-medium text-zinc-300"
                    >
                        Playing
                    </button>

                    <button
                        class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-2 text-sm font-medium text-zinc-300"
                    >
                        Finished
                    </button>

                    <button
                        class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-2 text-sm font-medium text-zinc-300"
                    >
                        Wishlist
                    </button>
                </div>

                <GameGrid :games="games" />
            </main>
        </div>
    </div>
</template>