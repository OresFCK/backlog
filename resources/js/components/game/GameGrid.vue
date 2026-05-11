<script setup>
import GameCard from './gameCard.vue'

defineProps({
    games: {
        type: Array,
        required: true,
    },
})
</script>

<template>
    <div
        class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5"
    >
        <GameCard
            v-for="game in games"
            :key="game.id"
            :game="game"
        />
    </div>
</template>