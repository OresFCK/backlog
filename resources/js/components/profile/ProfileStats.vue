<script setup>
import {
    ChartNoAxesColumnIncreasing,
    CheckCircle2,
    Library,
} from 'lucide-vue-next';

defineProps({
    gamesCount: Number,
    stats: Object,
});
</script>

<template>
    <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3">
        <div
            class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 p-4 sm:p-6"
        >
            <Library
                class="absolute top-4 right-4 h-10 w-10 text-zinc-800 sm:right-5 sm:h-14 sm:w-14"
            />

            <p
                class="text-xs font-semibold tracking-widest text-zinc-500 uppercase"
            >
                Total Library
            </p>

            <div class="mt-4 flex items-end gap-2">
                <p class="text-3xl font-bold sm:text-5xl">{{ gamesCount }}</p>
                <p class="pb-1 text-sm text-zinc-400">games</p>
            </div>

            <p class="mt-4 text-xs text-zinc-500 sm:mt-6">
                Steam + custom games
            </p>
        </div>

        <div
            class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 p-4 sm:p-6"
        >
            <CheckCircle2
                class="absolute top-4 right-4 h-10 w-10 text-zinc-800 sm:right-5 sm:h-14 sm:w-14"
            />

            <p
                class="text-xs font-semibold tracking-widest text-zinc-500 uppercase"
            >
                Completed Games
            </p>

            <div class="mt-4 flex items-end gap-2">
                <p class="text-3xl font-bold sm:text-5xl">
                    {{ stats.finished }}
                </p>
                <p class="pb-1 text-sm text-zinc-400">games</p>
            </div>

            <p class="mt-4 text-xs text-zinc-500 sm:mt-6">
                {{ stats.played }} played titles
            </p>
        </div>

        <div
            class="relative col-span-2 overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 p-4 sm:p-6 md:col-span-1"
        >
            <ChartNoAxesColumnIncreasing
                class="absolute top-4 right-4 h-10 w-10 text-zinc-800 sm:right-5 sm:h-14 sm:w-14"
            />

            <p
                class="text-xs font-semibold tracking-widest text-zinc-500 uppercase"
            >
                Completion Rate
            </p>

            <div class="mt-6 h-2 overflow-hidden rounded-full bg-zinc-800">
                <div
                    class="h-full rounded-full bg-white transition-all"
                    :style="{ width: `${stats.completionRate}%` }"
                />
            </div>

            <p class="mt-4 text-3xl font-bold sm:mt-6 sm:text-5xl">
                {{ stats.completionRate }}%
            </p>
            <p class="mt-6 text-xs text-zinc-500">
                {{ stats.finished }} out of {{ gamesCount }} games
            </p>
        </div>
    </div>
</template>
