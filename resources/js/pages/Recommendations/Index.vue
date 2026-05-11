<script setup>
import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import RecommendationCard from '@/components/Game/RecommendationCard.vue'

const recommendations = [
    {
        id: 1,
        game: {
            title: 'Hades',
            header_image_url:
                'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1145360/header.jpg',
        },
        reason:
            'Polecane, bo jest relatywnie krótka i ma bardzo dobre oceny.',
    },

    {
        id: 2,
        game: {
            title: 'Firewatch',
            header_image_url:
                'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/383870/header.jpg',
        },
        reason:
            'Idealna na krótkie sesje i szybkie ukończenie.',
    },
]
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar />

            <main class="flex-1 space-y-8 p-8">
                <div>
                    <h1 class="text-4xl font-bold text-white">
                        Recommendations
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        We picked these games for you.
                    </p>
                </div>

                <div class="space-y-8">
                    <RecommendationCard
                        v-for="recommendation in recommendations"
                        :key="recommendation.id"
                        :recommendation="recommendation"
                    />
                </div>
            </main>
        </div>
    </div>
</template>