<script setup>
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
});

const recommendationPercent = computed(() => {
    if (!props.stats.total_reviews) {
        return 0;
    }

    return Math.round(
        (props.stats.recommended_count / props.stats.total_reviews) * 100,
    );
});
</script>

<template>
    <div class="mt-5 grid grid-cols-2 gap-3 sm:mt-8 sm:grid-cols-3 sm:gap-4">
        <div
            class="col-span-2 rounded-xl border border-zinc-800 bg-zinc-900/80 p-3 sm:col-span-1 sm:rounded-2xl sm:p-5"
        >
            <div class="text-2xl font-black sm:text-3xl">
                {{ stats.average_rating || '—' }}
            </div>

            <div class="mt-1 text-xs font-bold text-zinc-500 uppercase">
                Avg rating
            </div>
        </div>

        <div
            class="rounded-xl border border-zinc-800 bg-zinc-900/80 p-3 sm:rounded-2xl sm:p-5"
        >
            <div class="text-2xl font-black sm:text-3xl">
                {{ stats.total_reviews }}
            </div>

            <div class="mt-1 text-xs font-bold text-zinc-500 uppercase">
                Reviews
            </div>
        </div>

        <div
            class="rounded-xl border border-zinc-800 bg-zinc-900/80 p-3 sm:rounded-2xl sm:p-5"
        >
            <div class="text-2xl font-black sm:text-3xl">
                {{ recommendationPercent }}%
            </div>

            <div class="mt-1 text-xs font-bold text-zinc-500 uppercase">
                Recommend
            </div>
        </div>
    </div>
</template>
