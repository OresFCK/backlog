<script setup>
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
    <div class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
        <h2 class="text-lg font-black">
            {{ game.title }} review breakdown
        </h2>

        <div class="mt-5 space-y-3 text-sm text-zinc-300">
            <div class="flex justify-between">
                <span>Recommended</span>
                <strong>{{ stats.recommended_count }}</strong>
            </div>

            <div class="flex justify-between">
                <span>Not recommended</span>
                <strong>{{ stats.not_recommended_count }}</strong>
            </div>
        </div>
    </div>
</template>