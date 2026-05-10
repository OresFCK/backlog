<script setup>
defineProps({
    recommendation: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <div
        class="relative overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900"
    >
        <img
            :src="recommendation.game.header_image_url"
            class="absolute inset-0 h-full w-full object-cover opacity-30"
        />

        <div
            class="absolute inset-0 bg-gradient-to-r from-black via-black/90 to-black/40"
        />

        <div class="relative z-10 flex flex-col gap-4 p-8">
            <div>
                <p class="text-sm font-medium text-indigo-400">
                    Recommended for you
                </p>

                <h2 class="mt-2 text-4xl font-bold text-white">
                    {{ recommendation.game.title }}
                </h2>

                <p class="mt-4 max-w-xl text-zinc-300">
                    {{ recommendation.reason }}
                </p>
            </div>

            <div class="flex items-center gap-4">
                <button
                    class="rounded-xl bg-white px-6 py-3 text-sm font-semibold text-black transition hover:bg-zinc-200"
                >
                    Play now
                </button>

                <button
                    class="rounded-xl border border-zinc-700 bg-white/5 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                >
                    Skip
                </button>
            </div>
        </div>
    </div>
</template>