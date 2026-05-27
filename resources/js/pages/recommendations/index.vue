<script setup>
import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

defineProps({
    user: Object,

    backlogRecommendations: Array,

    steamRecommendations: Array,

    friendsRanking: Array,

    globalRanking: Array,
})
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 space-y-10 p-8">
                <section>
                    <div class="mb-6">
                        <h1
                            class="text-4xl font-black text-white"
                        >
                            Recommendations
                        </h1>

                        <p
                            class="mt-2 text-zinc-400"
                        >
                            Personalized recommendations
                            powered by your community.
                        </p>
                    </div>
                </section>

                <RecommendationsSection
                    :friends-ranking="
                        friendsRanking
                    "

                    :global-ranking="
                        globalRanking
                    "
                />

                <RecommendationCarousel
                    title="From your backlog"

                    subtitle="Games you already own and should play next."

                    :items="
                        backlogRecommendations
                    "
                />

                <RecommendationCarousel
                    title="Steam discoveries"

                    subtitle="Games outside your backlog recommended by the community."

                    :items="
                        steamRecommendations
                    "
                />
            </main>
        </div>
    </div>
</template>