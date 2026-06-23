<script setup>
defineProps({
    game: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <div class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
        <h2 class="text-lg font-black">
            About this page
        </h2>

        <p class="mt-3 text-sm leading-6 text-zinc-400">
            This page collects public player reviews for {{ game.title }},
            including ratings, recommendations and platform-specific opinions.
        </p>
    </div>
</template>