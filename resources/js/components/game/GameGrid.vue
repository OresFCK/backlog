<script setup>
import GameCard from './GameCard.vue';

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
});

defineEmits(['toggle-game-selection']);
</script>

<template>
    <div
        class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 lg:gap-5 xl:grid-cols-5"
    >
        <div v-for="game in games" :key="game.id" class="relative">
            <button
                v-if="selectionMode"
                type="button"
                class="absolute top-2 right-2 z-20 flex h-7 w-7 items-center justify-center rounded-full border border-white/20 bg-black/70 text-sm text-white backdrop-blur transition hover:bg-black sm:top-3 sm:right-3 sm:h-8 sm:w-8"
                :class="{
                    'bg-white text-zinc-950 hover:bg-white':
                        selectedGameIds.includes(game.id),
                }"
                @click="$emit('toggle-game-selection', game.id)"
            >
                ✓
            </button>

            <GameCard :game="game" :selection-mode="selectionMode" />
        </div>
    </div>
</template>
