<script setup lang="ts">
import { computed, ref } from 'vue';

import RelatedGames from '@/components/game/RelatedGames.vue';
import PublicReviewModal from '@/components/games/PublicReviewModal.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import ReviewHero from '@/components/reviews/ReviewHero.vue';
import ReviewList from '@/components/reviews/ReviewList.vue';
import ReviewPageHeader from '@/components/reviews/ReviewPageHeader.vue';
import ReviewSidebar from '@/components/reviews/ReviewSidebar.vue';

const props = defineProps({
    game: {
        type: Object,
        required: true,
    },

    reviews: {
        type: Object,
        default: () => ({ data: [] }),
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
});

const selectedPlatform = ref('all');
const isReviewModalOpen = ref(false);

const filteredReviews = computed(() => {
    if (selectedPlatform.value === 'all') {
        return props.reviews.data;
    }

    return props.reviews.data.filter((review) => {
        return review.platform === selectedPlatform.value;
    });
});
</script>

<template>
    <div class="min-h-screen bg-zinc-950 text-white">
        <ReviewPageHeader
            :auth="auth"
            @write-review="isReviewModalOpen = true"
        />

        <ReviewHero :game="game" :stats="stats" />

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-10">
            <div
                class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:gap-8 xl:grid-cols-[minmax(0,1fr)_340px]"
            >
                <ReviewList
                    v-model:platform="selectedPlatform"
                    :game="game"
                    :stats="stats"
                    :reviews="filteredReviews"
                    :auth="auth"
                    @write-review="isReviewModalOpen = true"
                />

                <ReviewSidebar :game="game" :stats="stats" />
            </div>

            <PaginationLinks v-if="reviews.data.length" :pagination="reviews" />

            <RelatedGames class="mt-8 sm:mt-10" :games="relatedGames" />
        </main>

        <PublicReviewModal
            v-if="isReviewModalOpen"
            :game="game"
            @close="isReviewModalOpen = false"
        />
    </div>
</template>
