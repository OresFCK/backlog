<script setup>
import { Trophy, Calendar } from 'lucide-vue-next'

defineProps({
    game: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <div class="grid gap-5 md:grid-cols-3">
        <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">
                Playtime
            </p>

            <p class="mt-3 text-3xl font-black text-white">
                {{ game.playtime_hours ?? '—' }}h
            </p>

            <p class="mt-1 text-xs text-zinc-500">
                From Steam
            </p>
        </div>

        <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
            <Trophy class="h-6 w-6 text-zinc-400" />

            <p class="mt-3 text-3xl font-black text-white">
                {{ game.achievements_unlocked ?? '—' }}/{{ game.achievements_total ?? '—' }}
            </p>

            <p class="mt-1 text-xs text-zinc-500">
                Achievements
            </p>
        </div>

        <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
            <Calendar class="h-6 w-6 text-zinc-400" />

            <p class="mt-3 text-2xl font-black text-white">
                {{ game.release_date || '—' }}
            </p>

            <p class="mt-1 text-xs text-zinc-500">
                Release
            </p>
        </div>
    </div>
</template>