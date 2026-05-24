<script setup>
import { computed, ref } from 'vue'
import { X } from 'lucide-vue-next'

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

const isReviewModalOpen = computed(() => selectedReview.value !== null)

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

                <div class="space-y-6">
                    <article
                        v-for="review in reviews"
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
                                        {{
                                            review.user?.name ??
                                            'Unknown user'
                                        }}
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
                                </div>

                                <h3 class="mt-4 text-2xl font-black text-white">
                                    {{
                                        review.title ||
                                        'Untitled review'
                                    }}
                                </h3>

                                <p class="mt-4 whitespace-pre-line text-zinc-300">
                                    {{ truncatedBody(review.body) }}
                                </p>

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
                        v-if="!reviews.length"
                        class="rounded-3xl border border-dashed border-zinc-800 p-16 text-center"
                    >
                        <h2 class="text-2xl font-black text-white">
                            No reviews yet
                        </h2>

                        <p class="mt-3 text-zinc-400">
                            Public reviews will appear here.
                        </p>
                    </div>
                </div>
            </main>
        </div>

        <div
            v-if="isReviewModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6"
        >
            <div class="max-h-[85vh] w-full max-w-3xl overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-950 shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-800 p-6">
                    <div>
                        <h2 class="text-2xl font-black text-white">
                            {{
                                selectedReview.title ||
                                'Untitled review'
                            }}
                        </h2>

                        <p class="mt-2 text-sm text-zinc-400">
                            By
                            {{
                                selectedReview.user?.name ??
                                'Unknown user'
                            }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-900 hover:text-white"
                        @click="closeReviewModal"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="max-h-[65vh] overflow-y-auto p-6">
                    <div class="mb-5 flex flex-wrap gap-3">
                        <span
                            v-if="selectedReview.rating"
                            class="rounded-xl border border-zinc-700 bg-zinc-900 px-3 py-1 text-sm font-bold text-white"
                        >
                            {{ selectedReview.rating }}/10
                        </span>

                        <span
                            v-if="selectedReview.recommended"
                            class="rounded-xl bg-emerald-500/10 px-3 py-1 text-sm font-bold text-emerald-300"
                        >
                            Recommended
                        </span>
                    </div>

                    <p class="whitespace-pre-line text-zinc-300">
                        {{ selectedReview.body }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>