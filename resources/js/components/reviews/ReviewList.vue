<script setup>
import { computed } from 'vue';

import platformLabels from '@/constants/platformLabels';
import ReviewFilters from '@/components/reviews/ReviewFilters.vue';
import ReviewCard from '@/components/reviews/ReviewCard.vue';

const props = defineProps({
    game: {
        type: Object,
        required: true,
    },

    reviews: {
        type: Array,
        default: () => [],
    },

    allReviews: {
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

    platform: {
        type: String,
        default: 'all',
    },
});

defineEmits(['update:platform', 'write-review', 'read-more']);

const platforms = computed(() => {
    return Object.entries(props.stats.platforms || {}).map(
        ([value, count]) => ({
            value,
            count,
            label: platformLabels[value] || value,
        }),
    );
});
</script>

<template>
    <section
        class="min-w-0 space-y-6 sm:space-y-8"
        :aria-label="`${game.title} player reviews`"
    >
        <div
            class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4 sm:rounded-3xl sm:p-6"
        >
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black break-words sm:text-2xl">
                        {{ game.title }} player reviews
                    </h2>

                    <p class="mt-1 text-sm text-zinc-400">
                        Browse ratings and recommendations by platform.
                    </p>
                </div>

                <button
                    v-if="auth.user"
                    type="button"
                    class="w-full rounded-xl bg-white px-4 py-3 text-sm font-black text-zinc-950 hover:bg-zinc-200 sm:w-auto"
                    @click="$emit('write-review')"
                >
                    Add review
                </button>
            </div>

            <ReviewFilters
                :platforms="platforms"
                :platform="platform"
                @update:platform="$emit('update:platform', $event)"
            />
        </div>

        <div v-if="reviews.length" class="space-y-4">
            <ReviewCard
                v-for="review in reviews"
                :key="review.id"
                :review="review"
                :game="game"
                expand-inline
            />
        </div>

        <div
            v-else
            class="rounded-2xl border border-dashed border-zinc-700 bg-zinc-900 p-6 text-center sm:rounded-3xl sm:p-10"
        >
            <h2 class="text-xl font-black">No {{ game.title }} reviews yet</h2>

            <p class="mt-2 text-zinc-400">
                Be the first curator to review this game.
            </p>
        </div>
    </section>
</template>
