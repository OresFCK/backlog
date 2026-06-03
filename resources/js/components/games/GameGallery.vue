<script setup>
defineProps({
    screenshots: {
        type: Array,
        default: () => [],
    },
})
</script>

<template>
    <div v-if="screenshots.length">
        <h2 class="mb-4 text-2xl font-bold text-white">
            Gallery
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
            <img
                v-for="screenshot in screenshots"
                :key="screenshot"
                :src="screenshot"
                class="h-52 w-full rounded-2xl object-cover"
            />
        </div>
    </div>
</template>