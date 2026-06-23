<script setup>
import { computed } from 'vue'
import ReviewStats from '@/components/reviews/ReviewStats.vue'

const props = defineProps({
    game: {
        type: Object,
        required: true,
    },

    stats: {
        type: Object,
        required: true,
    },
})

const heroImage = computed(() => {
    return props.game.header_image_url || props.game.cover_url || null
})
</script>

<template>
    <section class="relative overflow-hidden border-b border-zinc-800">
        <div
            v-if="heroImage"
            class="absolute inset-0 bg-cover bg-center opacity-30 blur-sm"
            :style="{ backgroundImage: `url(${heroImage})` }"
        />

        <div class="absolute inset-0 bg-gradient-to-b from-zinc-950/60 via-zinc-950/90 to-zinc-950" />

        <div class="relative mx-auto grid max-w-7xl gap-8 px-6 py-14 lg:grid-cols-[260px_1fr]">
            <img
                v-if="game.cover_url"
                :src="game.cover_url"
                :alt="`${game.title} cover art`"
                class="aspect-[3/4] w-full rounded-3xl border border-zinc-800 object-cover shadow-2xl"
                loading="eager"
                fetchpriority="high"
            >

            <div class="flex flex-col justify-end">
                <div
                    v-if="game.genres?.length"
                    class="mb-4 flex flex-wrap gap-2"
                >
                    <span
                        v-for="genre in game.genres"
                        :key="genre"
                        class="rounded-full border border-zinc-700 bg-zinc-900/80 px-3 py-1 text-xs font-bold text-zinc-300"
                    >
                        {{ genre }}
                    </span>
                </div>

                <h1 class="text-5xl font-black tracking-tight">
                    {{ game.title }} Reviews
                </h1>

                <p
                    v-if="game.summary"
                    class="mt-4 max-w-3xl text-lg leading-8 text-zinc-300"
                >
                    {{ game.summary }}
                </p>

                <ReviewStats :stats="stats" />
            </div>
        </div>
    </section>
</template>