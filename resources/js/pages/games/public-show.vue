<script setup>
import { computed, ref } from 'vue'

import ReviewPageHeader from '@/components/reviews/ReviewPageHeader.vue'
import ReviewHero from '@/components/reviews/ReviewHero.vue'
import ReviewList from '@/components/reviews/ReviewList.vue'
import ReviewSidebar from '@/components/reviews/ReviewSidebar.vue'
import RelatedGames from '@/components/game/RelatedGames.vue'
import PublicReviewModal from '@/components/games/PublicReviewModal.vue'

const props = defineProps({
    game: {
        type: Object,
        required: true,
    },

    reviews: {
        type: Array,
        default: () => [],
    },

    stats: {
        type: Object,
        required: true,
    },

    auth: {
        type: Object,
        default: () => ({
            user: null,
        }),
    },

    relatedGames: {
        type: Array,
        default: () => [],
    },
})

const selectedPlatform = ref('all')
const isReviewModalOpen = ref(false)

const filteredReviews = computed(() => {
    if (selectedPlatform.value === 'all') {
        return props.reviews
    }

    return props.reviews.filter((review) => {
        return review.platform === selectedPlatform.value
    })
})
</script>

<template>
    <div class="min-h-screen bg-zinc-950 text-white">
        <ReviewPageHeader
            :auth="auth"
            @write-review="isReviewModalOpen = true"
        />

        <ReviewHero
            :game="game"
            :stats="stats"
        />

        <main class="mx-auto max-w-7xl px-6 py-10">
            <div class="grid gap-8 lg:grid-cols-[1fr_340px]">
                <ReviewList
                    v-model:platform="selectedPlatform"
                    :game="game"
                    :stats="stats"
                    :reviews="filteredReviews"
                    :auth="auth"
                    @write-review="isReviewModalOpen = true"
                />

                <ReviewSidebar
                    :game="game"
                    :stats="stats"
                />
            </div>

            <RelatedGames
                class="mt-10"
                :games="relatedGames"
            />
        </main>

        <PublicReviewModal
            v-if="isReviewModalOpen"
            :game="game"
            @close="isReviewModalOpen = false"
        />
    </div>
</template>