<script setup>
import { computed } from 'vue'
import { Trophy, Calendar } from 'lucide-vue-next'

const props = defineProps({
    game: {
        type: Object,
        required: true,
    },
})

const playtimeLabel = computed(() =>
    props.game.is_custom ? 'Manual' : 'From Steam'
)

const achievementsLabel = computed(() =>
    props.game.is_custom ? 'Manual achievements' : 'Steam achievements'
)

const playtimeHours = computed(() => {
    if (
        props.game.playtime_hours !== null &&
        props.game.playtime_hours !== undefined &&
        props.game.playtime_hours !== ''
    ) {
        return props.game.playtime_hours
    }

    if (props.game.playtime_forever) {
        return Math.round((props.game.playtime_forever / 60) * 10) / 10
    }

    return null
})

const achievementText = computed(() => {
    const unlocked = props.game.achievements_unlocked
    const total = props.game.achievements_total

    if (
        unlocked === null ||
        unlocked === undefined ||
        total === null ||
        total === undefined ||
        Number(total) <= 0
    ) {
        return '—'
    }

    return `${unlocked}/${total}`
})

const achievementPercent = computed(() => {
    const unlocked = Number(props.game.achievements_unlocked ?? 0)
    const total = Number(props.game.achievements_total ?? 0)

    if (!total) {
        return null
    }

    return Math.round((unlocked / total) * 100)
})
</script>

<template>
    <div class="grid gap-5 md:grid-cols-3">
        <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">
                Playtime
            </p>

            <p class="mt-3 text-3xl font-black text-white">
                <template v-if="playtimeHours !== null">
                    {{ playtimeHours }}h
                </template>

                <template v-else>
                    —
                </template>
            </p>

            <p class="mt-1 text-xs text-zinc-500">
                {{ playtimeLabel }}
            </p>
        </div>

        <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
            <Trophy class="h-6 w-6 text-zinc-400" />

            <p class="mt-3 text-3xl font-black text-white">
                {{ achievementText }}
            </p>

            <p class="mt-1 text-xs text-zinc-500">
                {{ achievementsLabel }}
                <span v-if="achievementPercent !== null">
                    · {{ achievementPercent }}%
                </span>
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