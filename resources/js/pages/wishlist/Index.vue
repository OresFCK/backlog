<script setup>
import { computed } from 'vue'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import GameGrid from '@/components/game/GameGrid.vue'

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

const wishlistGames = computed(() => {
    return props.games.map((game) => ({
        id: game.appid,

        title: game.name,

        cover_url: `https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/${game.appid}/library_600x900.jpg`,

        status: 'wishlist',

        average_playtime_minutes:
            game.playtime_forever ?? null,

        platform: 'Steam',

        rating: Math.floor(Math.random() * 20) + 80,
    }))
})
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 p-8">
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-white">
                        Wishlist
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Games imported from your Steam account.
                    </p>
                </div>

                <GameGrid :games="wishlistGames" />

                <div
                    v-if="!wishlistGames.length"
                    class="rounded-3xl border border-zinc-800 bg-zinc-900/50 p-10 text-center"
                >
                    <h3 class="text-2xl font-bold text-white">
                        No wishlist games found
                    </h3>

                    <p class="mt-3 text-zinc-400">
                        Your Steam wishlist is empty or could not
                        be loaded.
                    </p>
                </div>
            </main>
        </div>
    </div>
</template>