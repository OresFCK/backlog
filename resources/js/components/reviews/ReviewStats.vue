<script setup>
import { computed } from 'vue'

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
})

const recommendationPercent = computed(() => {
    if (!props.stats.total_reviews) {
        return 0
    }

    return Math.round(
        (props.stats.recommended_count / props.stats.total_reviews) * 100
    )
})
</script>

<template>
    <div class="mt-8 grid gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-5">
            <div class="text-3xl font-black">
                {{ stats.average_rating || '—' }}
            </div>

            <div class="mt-1 text-xs font-bold uppercase text-zinc-500">
                Avg rating
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-5">
            <div class="text-3xl font-black">
                {{ stats.total_reviews }}
            </div>

            <div class="mt-1 text-xs font-bold uppercase text-zinc-500">
                Reviews
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-5">
            <div class="text-3xl font-black">
                {{ recommendationPercent }}%
            </div>

            <div class="mt-1 text-xs font-bold uppercase text-zinc-500">
                Recommend
            </div>
        </div>
    </div>
</template>