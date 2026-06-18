<script setup>
import GameCard from './GameCard.vue'

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

defineEmits([
    'toggle-game-selection',
])
</script>

<template>
    <div
        class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5"
    >
        <div
            v-for="game in games"
            :key="game.id"
            class="relative"
        >
            <button
                v-if="selectionMode"
                type="button"
                class="absolute right-3 top-3 z-20 flex h-8 w-8 items-center justify-center rounded-full border border-white/20 bg-black/70 text-white backdrop-blur transition hover:bg-black"
                :class="{
                    'bg-white text-zinc-950 hover:bg-white':
                        selectedGameIds.includes(game.id),
                }"
                @click="
                    $emit(
                        'toggle-game-selection',
                        game.id
                    )
                "
            >
                ✓
            </button>

            <GameCard :game="game" />
        </div>
    </div>
</template>