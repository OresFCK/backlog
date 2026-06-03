<script setup>
defineProps({
    gamesCount: Number,
    stats: Object,
})
</script>

<template>
    <div class="grid gap-4 md:grid-cols-3">
        <div class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
            <div class="absolute right-5 top-3 text-7xl text-zinc-800">
                ▦
            </div>

            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
                Total Library
            </p>

            <div class="mt-4 flex items-end gap-2">
                <p class="text-5xl font-bold">
                    {{ gamesCount }}
                </p>

                <p class="pb-1 text-sm text-zinc-400">
                    games
                </p>
            </div>

            <p class="mt-6 text-xs text-zinc-500">
                Steam + custom games
            </p>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
            <div class="absolute right-5 top-2 text-7xl text-zinc-800">
                ✓
            </div>

            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
                Completed Games
            </p>

            <div class="mt-4 flex items-end gap-2">
                <p class="text-5xl font-bold">
                    {{ stats.finished }}
                </p>

                <p class="pb-1 text-sm text-zinc-400">
                    games
                </p>
            </div>

            <p class="mt-6 text-xs text-zinc-500">
                {{ stats.played }} played titles
            </p>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
                Completion Rate
            </p>

            <div class="mt-6 h-2 overflow-hidden rounded-full bg-zinc-800">
                <div
                    class="h-full rounded-full bg-white transition-all"
                    :style="{ width: `${stats.completionRate}%` }"
                />
            </div>

            <p class="mt-6 text-5xl font-bold">
                {{ stats.completionRate }}%
            </p>

            <p class="mt-6 text-xs text-zinc-500">
                {{ stats.finished }} out of {{ gamesCount }} games
            </p>
        </div>
    </div>
</template>