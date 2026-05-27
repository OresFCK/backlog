<script setup>
import { computed } from 'vue'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import RecommendationsSection from '@/components/recommendations/RecommendationsSection.vue'
import RecommendationCarousel from '@/components/recommendations/RecommendationCarousel.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },

    backlogRecommendations: {
        type: Array,
        default: () => [],
    },

    steamRecommendations: {
        type: Array,
        default: () => [],
    },

    friendsRanking: {
        type: Array,
        default: () => [],
    },

    globalRanking: {
        type: Array,
        default: () => [],
    },
})

const topRecommendation = computed(() => {
    return [
        ...props.backlogRecommendations,
        ...props.steamRecommendations,
        ...props.friendsRanking,
        ...props.globalRanking,
    ]
        .sort((a, b) => Number(b.score ?? 0) - Number(a.score ?? 0))[0]
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
                        <h1 class="text-4xl font-black text-white">
                            Recommendations
                        </h1>

                        <p class="mt-2 text-zinc-400">
                            Personalized recommendations powered by your community.
                        </p>
                    </div>
                </section>

                <section
                    v-if="topRecommendation"
                    class="relative overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900 p-8"
                >
                    <img
                        :src="topRecommendation.game.header_image_url"
                        class="absolute inset-0 h-full w-full object-cover opacity-25"
                    />

                    <div class="absolute inset-0 bg-gradient-to-r from-black via-black/90 to-black/50" />

                    <div class="relative z-10 max-w-3xl">
                        <p class="text-sm font-bold text-indigo-300">
                            Top recommendation
                        </p>

                        <h2 class="mt-3 text-5xl font-black text-white">
                            {{ topRecommendation.game.title }}
                        </h2>

                        <p class="mt-4 text-zinc-300">
                            {{ topRecommendation.reason }}
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <span class="rounded-full bg-indigo-500/10 px-4 py-2 text-sm font-bold text-indigo-300">
                                Score {{ topRecommendation.score }}
                            </span>

                            <span class="rounded-full bg-yellow-500/10 px-4 py-2 text-sm font-bold text-yellow-300">
                                ★ {{ topRecommendation.average_rating }}/10
                            </span>
                        </div>
                    </div>
                </section>

                <RecommendationsSection
                    :friends-ranking="friendsRanking"
                    :global-ranking="globalRanking"
                />

                <RecommendationCarousel
                    title="From your backlog"
                    subtitle="Games you already own and should play next."
                    :items="backlogRecommendations"
                />

                <RecommendationCarousel
                    title="Steam discoveries"
                    subtitle="Games outside your backlog recommended by the community."
                    :items="steamRecommendations"
                />
            </main>
        </div>
    </div>
</template>