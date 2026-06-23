<script setup>
defineProps({
    games: {
        type: Array,
        required: true,
    },

    selectedGameIds: {
        type: Array,
        default: () => [],
    },

    selectionMode: {
        type: Boolean,
        default: false,
    },
})

defineEmits(['toggle-game-selection'])
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950">
        <div
            v-for="game in games"
            :key="game.id"
            class="flex items-center gap-4 border-b border-zinc-800 bg-zinc-900/40 p-4 transition last:border-b-0 hover:bg-zinc-900"
        >
            <button
                v-if="selectionMode"
                type="button"
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-white/20 bg-black/70 text-white backdrop-blur transition hover:bg-black"
                :class="{
                    'bg-white text-zinc-950 hover:bg-white':
                        selectedGameIds.includes(game.id),
                }"
                @click="$emit('toggle-game-selection', game.id)"
            >
                ✓
            </button>

            <img
                :src="game.cover_url"
                :alt="game.title"
                class="h-20 w-14 shrink-0 rounded-lg object-cover"
            >

            <div class="min-w-0 flex-1">
                <h3 class="truncate text-base font-bold text-white">
                    {{ game.title }}
                </h3>

                <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-zinc-400">
                    <span>{{ game.platform }}</span>

                    <span class="text-zinc-700">•</span>

                    <span class="flex items-center gap-2">
                        <span
                            class="h-2.5 w-2.5 rounded-full"
                            :style="{ backgroundColor: game.status_color }"
                        />
                        {{ game.status }}
                    </span>
                </div>
            </div>

            <div class="hidden text-right sm:block">
                <p class="text-xs font-bold uppercase tracking-wide text-zinc-500">
                    Playtime
                </p>

                <p class="mt-1 text-sm font-bold text-white">
                    {{
                        Math.round(
                            game.average_playtime_minutes / 60
                        )
                    }}h
                </p>
            </div>

            <div class="hidden min-w-16 text-right sm:block">
                <p class="text-xs font-bold uppercase tracking-wide text-zinc-500">
                    Rating
                </p>

                <p class="mt-1 text-sm font-bold text-white">
                    {{ game.rating ?? '-' }}
                </p>
            </div>
        </div>

        <div
            v-if="!games.length"
            class="p-8 text-center text-sm text-zinc-500"
        >
            No games found.
        </div>
    </div>
</template>