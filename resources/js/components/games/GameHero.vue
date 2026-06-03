<script setup>
defineProps({
    game: {
        type: Object,
        required: true,
    },
})

defineEmits(['create-review'])
</script>

<template>
    <div
        class="relative min-h-[360px] bg-cover bg-center"
        :style="{
            backgroundImage: game.header_image
                ? `linear-gradient(to right, rgba(9,9,11,.95), rgba(9,9,11,.55)), url(${game.header_image})`
                : null,
        }"
    >
        <div class="flex min-h-[360px] flex-col justify-between p-10">
            <div>
                <div class="mb-4 flex flex-wrap gap-2">
                    <span
                        v-for="genre in game.genres"
                        :key="genre"
                        class="rounded-full border border-zinc-700 bg-zinc-950/70 px-3 py-1 text-xs font-semibold text-zinc-300"
                    >
                        {{ genre }}
                    </span>
                </div>

                <h1 class="max-w-3xl text-5xl font-black text-white">
                    {{ game.title }}
                </h1>

                <p class="mt-4 max-w-2xl text-zinc-300">
                    {{ game.description || 'No description available.' }}
                </p>
            </div>

            <div class="mt-8">
                <button
                    type="button"
                    class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                    @click="$emit('create-review')"
                >
                    Create public review
                </button>
            </div>
        </div>
    </div>
</template>