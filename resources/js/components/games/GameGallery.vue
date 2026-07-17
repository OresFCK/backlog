<script setup>
defineProps({
    screenshots: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div v-if="screenshots.length">
        <h2 class="mb-3 text-xl font-bold text-white sm:mb-4 sm:text-2xl">
            Gallery
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
            <img
                v-for="screenshot in screenshots"
                :key="screenshot"
                :src="screenshot"
                class="aspect-video h-auto w-full rounded-xl object-cover sm:rounded-2xl"
            />
        </div>
    </div>
</template>
