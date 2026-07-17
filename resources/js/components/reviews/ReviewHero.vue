<script setup>
import { computed } from 'vue';
import ReviewStats from '@/components/reviews/ReviewStats.vue';

const props = defineProps({
    game: {
        type: Object,
        required: true,
    },

    stats: {
        type: Object,
        required: true,
    },
});

const heroImage = computed(() => {
    return props.game.header_image_url || props.game.cover_url || null;
});
</script>

<template>
    <section class="relative overflow-hidden border-b border-zinc-800">
        <div
            v-if="heroImage"
            class="absolute inset-0 bg-cover bg-center opacity-30 blur-sm"
            :style="{ backgroundImage: `url(${heroImage})` }"
        />

        <div
            class="absolute inset-0 bg-gradient-to-b from-zinc-950/60 via-zinc-950/90 to-zinc-950"
        />

        <div
            class="relative mx-auto grid max-w-7xl gap-5 px-4 py-6 sm:gap-8 sm:px-6 sm:py-10 lg:grid-cols-[240px_minmax(0,1fr)] lg:py-14 xl:grid-cols-[260px_minmax(0,1fr)]"
        >
            <img
                v-if="game.cover_url"
                :src="game.cover_url"
                :alt="`${game.title} cover art`"
                class="mx-auto aspect-[3/4] w-36 rounded-2xl border border-zinc-800 object-cover shadow-2xl sm:w-48 sm:rounded-3xl lg:w-full"
                loading="eager"
                fetchpriority="high"
            />

            <div class="flex flex-col justify-end">
                <div
                    v-if="game.genres?.length"
                    class="mb-3 flex flex-wrap justify-center gap-2 lg:mb-4 lg:justify-start"
                >
                    <span
                        v-for="genre in game.genres"
                        :key="genre"
                        class="rounded-full border border-zinc-700 bg-zinc-900/80 px-3 py-1 text-xs font-bold text-zinc-300"
                    >
                        {{ genre }}
                    </span>
                </div>

                <h1
                    class="text-center text-3xl font-black tracking-tight break-words sm:text-4xl lg:text-left lg:text-5xl"
                >
                    {{ game.title }} Reviews
                </h1>

                <p
                    v-if="game.summary"
                    class="mt-3 max-w-3xl text-center text-sm leading-6 text-zinc-300 sm:mt-4 sm:text-base sm:leading-7 lg:text-left lg:text-lg lg:leading-8"
                >
                    {{ game.summary }}
                </p>

                <ReviewStats :stats="stats" />
            </div>
        </div>
    </section>
</template>
