<script setup>
import {
    computed,
    ref,
} from 'vue'

import { router } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import ReviewsFilters from '@/components/reviews/ReviewsFilters.vue'
import ReviewCard from '@/components/reviews/ReviewCard.vue'
import ReviewModal from '@/components/reviews/ReviewModal.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },

    reviews: {
        type: Array,
        default: () => [],
    },
})

const selectedReview = ref(null)

const filters = ref({
    user: '',
    game: '',
    rating: '',
    recommendation: '',
})

const filteredReviews = computed(() => {
    return props.reviews.filter((review) => {
        const userName = String(review.user?.name ?? '').toLowerCase()

        const gameTitle = String(
            review.game_title ?? review.title ?? ''
        ).toLowerCase()

        const matchesUser =
            !filters.value.user ||
            userName.includes(filters.value.user.toLowerCase())

        const matchesGame =
            !filters.value.game ||
            gameTitle.includes(filters.value.game.toLowerCase())

        const matchesRating =
            !filters.value.rating ||
            Number(review.rating) === Number(filters.value.rating)

        const matchesRecommendation =
            !filters.value.recommendation ||
            (
                filters.value.recommendation === 'recommended' &&
                review.recommended
            ) ||
            (
                filters.value.recommendation === 'not_recommended' &&
                review.not_recommended
            )

        return (
            matchesUser &&
            matchesGame &&
            matchesRating &&
            matchesRecommendation
        )
    })
})

const clearFilters = () => {
    filters.value = {
        user: '',
        game: '',
        rating: '',
        recommendation: '',
    }
}

const openReviewModal = (review) => {
    selectedReview.value = review
}

const closeReviewModal = () => {
    selectedReview.value = null
}

const vote = (review, value) => {
    router.post(
        `/reviews/${review.id}/vote`,
        { value },
        { preserveScroll: true }
    )
}

const removeVote = (review) => {
    router.delete(
        `/reviews/${review.id}/vote`,
        { preserveScroll: true }
    )
}

const toggleVote = (review, value) => {
    if (review.user_vote === value) {
        removeVote(review)

        return
    }

    vote(review, value)
}

const toggleFeatured = (review) => {
    router.post(
        `/reviews/${review.id}/feature`,
        {},
        { preserveScroll: true }
    )
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 p-8">
                <div class="mb-8">
                    <h1 class="text-4xl font-black text-white">
                        Reviews
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Public reviews from your community.
                    </p>
                </div>

                <ReviewsFilters
                    v-model:filters="filters"
                    :shown-count="filteredReviews.length"
                    :total-count="reviews.length"
                    @clear="clearFilters"
                />

                <div class="space-y-6">
                    <ReviewCard
                        v-for="review in filteredReviews"
                        :key="review.id"
                        :review="review"
                        @read-more="openReviewModal"
                        @toggle-vote="toggleVote"
                        @toggle-featured="toggleFeatured"
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
                </div>
            </main>
        </div>

        <ReviewModal
            v-if="selectedReview"
            :review="selectedReview"
            @close="closeReviewModal"
        />
    </div>
</template>