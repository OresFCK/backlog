<script setup>
import ReviewBreakdown from '@/components/reviews/ReviewBreakdown.vue'
import ReviewAbout from '@/components/reviews/ReviewAbout.vue'

defineProps({
    game: {
        type: Object,
        required: true,
    },

    stats: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <aside
        class="space-y-4"
        aria-label="Game review summary"
    >
        <ReviewBreakdown
            :game="game"
            :stats="stats"
        />

        <ReviewAbout :game="game" />
    </aside>
</template>