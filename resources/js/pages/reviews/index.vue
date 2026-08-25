<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import PublicReviewModal from '@/components/games/PublicReviewModal.vue';
import Sidebar from '@/components/layout/Sidebar.vue';
import Topbar from '@/components/layout/Topbar.vue';

import PaginationLinks from '@/components/PaginationLinks.vue';
import ReviewCard from '@/components/reviews/ReviewCard.vue';
import ReviewModal from '@/components/reviews/ReviewModal.vue';
import ReviewsFilters from '@/components/reviews/ReviewsFilters.vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },

    reviews: {
        type: Object,
        default: () => ({ data: [], total: 0 }),
    },

    pageTitle: {
        type: String,
        default: 'Reviews',
    },

    pageDescription: {
        type: String,
        default: 'Public reviews from your community.',
    },

    isMyReviews: {
        type: Boolean,
        default: false,
    },
});

const selectedReview = ref(null);
const reviewToEdit = ref(null);

const filters = ref({
    user: '',
    game: '',
    rating: '',
    recommendation: '',
});

const filteredReviews = computed(() => {
    return props.reviews.data.filter((review) => {
        const userName = String(review.user?.name ?? '').toLowerCase();

        const gameTitle = String(
            review.game_title ?? review.title ?? '',
        ).toLowerCase();

        const matchesUser =
            !filters.value.user ||
            userName.includes(filters.value.user.toLowerCase());

        const matchesGame =
            !filters.value.game ||
            gameTitle.includes(filters.value.game.toLowerCase());

        const matchesRating =
            !filters.value.rating ||
            Number(review.rating) === Number(filters.value.rating);

        const matchesRecommendation =
            !filters.value.recommendation ||
            (filters.value.recommendation === 'recommended' &&
                review.recommended) ||
            (filters.value.recommendation === 'not_recommended' &&
                review.not_recommended);

        return (
            matchesUser && matchesGame && matchesRating && matchesRecommendation
        );
    });
});

const clearFilters = () => {
    filters.value = {
        user: '',
        game: '',
        rating: '',
        recommendation: '',
    };
};

const openReviewModal = (review) => {
    selectedReview.value = review;
};

const closeReviewModal = () => {
    selectedReview.value = null;
};

const openEditModal = (review) => {
    reviewToEdit.value = review;
};

const closeEditModal = () => {
    reviewToEdit.value = null;
};

const vote = (review, value) => {
    router.post(
        `/reviews/${review.id}/vote`,
        {
            value,
        },
        {
            preserveScroll: true,
        },
    );
};

const removeVote = (review) => {
    router.delete(`/reviews/${review.id}/vote`, {
        preserveScroll: true,
    });
};

const toggleVote = (review, value) => {
    if (review.user_vote === value) {
        removeVote(review);

        return;
    }

    vote(review, value);
};

const toggleFeatured = (review) => {
    router.post(
        `/reviews/${review.id}/feature`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const reportReview = (review) => {
    const reason = window.prompt('Why are you reporting this review?');

    if (reason === null) {
        return;
    }

    router.post(
        `/reviews/${review.id}/report`,
        {
            reason,
        },
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 p-8">
                <div class="mb-8">
                    <h1 class="text-4xl font-black text-white">
                        {{ pageTitle }}
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        {{ pageDescription }}
                    </p>
                </div>

                <ReviewsFilters
                    v-model:filters="filters"
                    :shown-count="filteredReviews.length"
                    :total-count="reviews.total"
                    @clear="clearFilters"
                />

                <div class="space-y-6">
                    <ReviewCard
                        v-for="review in filteredReviews"
                        :key="review.id"
                        :review="review"
                        :allow-edit="isMyReviews"
                        @read-more="openReviewModal"
                        @toggle-vote="toggleVote"
                        @toggle-featured="toggleFeatured"
                        @report-review="reportReview"
                        @edit-review="openEditModal"
                    />

                    <div
                        v-if="!filteredReviews.length"
                        class="rounded-3xl border border-dashed border-zinc-800 p-16 text-center"
                    >
                        <h2 class="text-2xl font-black text-white">
                            No reviews found
                        </h2>

                        <p class="mt-3 text-zinc-400">
                            Try changing your filters.
                        </p>
                    </div>
                    <PaginationLinks
                        v-if="reviews.data.length"
                        :pagination="reviews"
                    />
                </div>
            </main>
        </div>

        <ReviewModal
            v-if="selectedReview"
            :review="selectedReview"
            @close="closeReviewModal"
        />

        <PublicReviewModal
            v-if="reviewToEdit"
            :review-id="reviewToEdit.id"
            :game="{
                id: reviewToEdit.game_id,
                database_id: reviewToEdit.game_id,
                title: reviewToEdit.game_title,
                source: reviewToEdit.source,
                steam_app_id:
                    reviewToEdit.source === 'steam'
                        ? reviewToEdit.source_game_id
                        : null,
                igdb_id:
                    reviewToEdit.source === 'igdb'
                        ? reviewToEdit.source_game_id
                        : null,
            }"
            :review-title="reviewToEdit.title"
            :note="reviewToEdit.body"
            :rating="String(reviewToEdit.rating ?? '')"
            :platform="reviewToEdit.platform ?? ''"
            :time-to-beat-hours="reviewToEdit.time_to_beat_hours ?? ''"
            :recommended="reviewToEdit.recommended"
            :not-recommended="reviewToEdit.not_recommended"
            :featured-on-profile="reviewToEdit.is_featured_on_profile"
            :image-layout="reviewToEdit.image_layout ?? 'grid'"
            editing
            @close="closeEditModal"
        />
    </div>
</template>
