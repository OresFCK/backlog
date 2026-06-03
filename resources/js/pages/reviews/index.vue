<script setup>
import {
    computed,
    ref,
} from 'vue'

import { router } from '@inertiajs/vue3'
import { Star, X } from 'lucide-vue-next'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

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
        const userName = String(
            review.user?.name ?? ''
        ).toLowerCase()

        const gameTitle = String(
            review.game_title
            ?? review.title
            ?? ''
        ).toLowerCase()

        const matchesUser =
            !filters.value.user ||
            userName.includes(
                filters.value.user.toLowerCase()
            )

        const matchesGame =
            !filters.value.game ||
            gameTitle.includes(
                filters.value.game.toLowerCase()
            )

        const matchesRating =
            !filters.value.rating ||
            Number(review.rating) ===
            Number(filters.value.rating)

        const matchesRecommendation =
            !filters.value.recommendation ||
            (
                filters.value.recommendation ===
                'recommended' &&
                review.recommended
            ) ||
            (
                filters.value.recommendation ===
                'not_recommended' &&
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

const shouldTruncate = (body) => {
    return String(body ?? '').length > 420
}

const truncatedBody = (body) => {
    const text = String(body ?? '')

    if (text.length <= 420) {
        return text
    }

    return `${text.slice(0, 420)}...`
}

const vote = (review, value) => {
    router.post(
        `/reviews/${review.id}/vote`,
        {
            value,
        },
        {
            preserveScroll: true,
        }
    )
}

const removeVote = (review) => {
    router.delete(
        `/reviews/${review.id}/vote`,
        {
            preserveScroll: true,
        }
    )
}

const toggleVote = (
    review,
    value
) => {
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
        {
            preserveScroll: true,
        }
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

                <div class="mb-6 rounded-3xl border border-zinc-800 bg-zinc-900 p-5">
                    <div class="grid gap-4 md:grid-cols-4">
                        <input
                            v-model="filters.user"
                            type="text"
                            placeholder="Filter by user"
                            class="rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                        />

                        <input
                            v-model="filters.game"
                            type="text"
                            placeholder="Filter by game"
                            class="rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                        />

                        <select
                            v-model="filters.rating"
                            class="rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-600"
                        >
                            <option value="">
                                Any rating
                            </option>

                            <option
                                v-for="rating in 10"
                                :key="rating"
                                :value="rating"
                            >
                                {{ rating }}/10
                            </option>
                        </select>

                        <select
                            v-model="filters.recommendation"
                            class="rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-600"
                        >
                            <option value="">
                                Any recommendation
                            </option>

                            <option value="recommended">
                                Recommended
                            </option>

                            <option value="not_recommended">
                                Not Recommended
                            </option>
                        </select>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <p class="text-sm text-zinc-500">
                            Showing {{ filteredReviews.length }} of {{ reviews.length }} reviews
                        </p>

                        <button
                            type="button"
                            class="rounded-xl border border-zinc-800 px-4 py-2 text-sm font-bold text-zinc-300 transition hover:bg-zinc-950 hover:text-white"
                            @click="clearFilters"
                        >
                            Clear filters
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    <article
                        v-for="review in filteredReviews"
                        :key="review.id"
                        class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6"
                    >
                        <div class="flex items-start gap-4">
                            <img
                                v-if="review.user?.avatar"
                                :src="review.user.avatar"
                                class="h-14 w-14 rounded-2xl object-cover"
                            />

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h2 class="text-lg font-bold text-white">
                                        {{ review.user?.name ?? 'Unknown user' }}
                                    </h2>

                                    <span class="text-sm text-zinc-500">
                                        {{ review.created_at }}
                                    </span>

                                    <span
                                        v-if="review.rating"
                                        class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-1 text-sm font-bold text-white"
                                    >
                                        {{ review.rating }}/10
                                    </span>

                                    <span
                                        v-if="review.recommended"
                                        class="rounded-xl bg-emerald-500/10 px-3 py-1 text-sm font-bold text-emerald-300"
                                    >
                                        Recommended
                                    </span>

                                    <span
                                        v-if="review.not_recommended"
                                        class="rounded-xl bg-red-500/10 px-3 py-1 text-sm font-bold text-red-300"
                                    >
                                        Not Recommended
                                    </span>
                                </div>

                                <p class="mt-4 text-sm font-bold text-indigo-300">
                                    {{ review.game_title || 'Unknown game' }}
                                </p>

                                <h3 class="mt-1 text-2xl font-black text-white">
                                    {{ review.title || 'Untitled review' }}
                                </h3>

                                <p class="mt-4 whitespace-pre-line text-zinc-300">
                                    {{ truncatedBody(review.body) }}
                                </p>

                                <div class="mt-5 flex flex-wrap items-center gap-3">
                                    <template v-if="review.can_vote">
                                        <button
                                            type="button"
                                            class="rounded-xl border px-3 py-1 text-sm font-bold transition"
                                            :class="
                                                review.user_vote === 1
                                                    ? 'border-emerald-500 bg-emerald-500/10 text-emerald-300'
                                                    : 'border-zinc-700 bg-zinc-950 text-zinc-300 hover:text-white'
                                            "
                                            @click="toggleVote(review, 1)"
                                        >
                                            +1
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-xl border px-3 py-1 text-sm font-bold transition"
                                            :class="
                                                review.user_vote === -1
                                                    ? 'border-red-500 bg-red-500/10 text-red-300'
                                                    : 'border-zinc-700 bg-zinc-950 text-zinc-300 hover:text-white'
                                            "
                                            @click="toggleVote(review, -1)"
                                        >
                                            -1
                                        </button>
                                    </template>

                                    <span class="text-sm font-bold text-zinc-400">
                                        Score: {{ review.votes_score ?? 0 }}
                                    </span>

                                    <button
                                        v-if="review.is_owner"
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-xl border px-3 py-1 text-sm font-bold transition"
                                        :class="
                                            review.is_featured_on_profile
                                                ? 'border-indigo-500/40 bg-indigo-500/10 text-indigo-300'
                                                : 'border-zinc-700 bg-zinc-950 text-zinc-300 hover:text-white'
                                        "
                                        @click="toggleFeatured(review)"
                                    >
                                        <Star class="h-4 w-4" />

                                        {{
                                            review.is_featured_on_profile
                                                ? 'Featured on Profile'
                                                : 'Feature on Profile'
                                        }}
                                    </button>
                                </div>

                                <button
                                    v-if="shouldTruncate(review.body)"
                                    type="button"
                                    class="mt-4 text-sm font-bold text-white underline underline-offset-4 transition hover:text-zinc-300"
                                    @click="openReviewModal(review)"
                                >
                                    Read more
                                </button>
                            </div>
                        </div>
                    </article>

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

        <div
            v-if="selectedReview"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6"
        >
            <div class="w-full max-w-3xl rounded-3xl border border-zinc-800 bg-zinc-950 p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-indigo-300">
                            {{ selectedReview.game_title }}
                        </p>

                        <h2 class="mt-1 text-2xl font-black text-white">
                            {{ selectedReview.title || 'Untitled review' }}
                        </h2>
                    </div>

                    <button
                        type="button"
                        class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-900 hover:text-white"
                        @click="closeReviewModal"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <p class="mt-6 whitespace-pre-line text-zinc-300">
                    {{ selectedReview.body }}
                </p>
            </div>
        </div>
    </div>
</template>