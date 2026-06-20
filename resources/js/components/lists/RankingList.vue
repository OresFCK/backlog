<script setup>
import RankingItem from './RankingItem.vue'

defineProps({
    items: Array,
    draggedIndex: Number,
})

defineEmits([
    'remove',
    'move',
    'drag-start',
    'drop',
    'drag-end',
])
</script>

<template>
    <div class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-xl font-bold">
                Ranking
            </h2>

            <p class="text-sm text-zinc-500">
                Drag items to reorder.
            </p>
        </div>

        <div
            v-if="items.length"
            class="space-y-4"
        >
            <RankingItem
                v-for="(item, index) in items"
                :key="item.id"
                :item="item"
                :index="index"
                :is-first="index === 0"
                :is-last="index === items.length - 1"
                :is-dragging="draggedIndex === index"
                draggable="true"
                @dragstart="$emit('drag-start', index)"
                @dragover.prevent
                @drop="$emit('drop', index)"
                @dragend="$emit('drag-end')"
                @remove="$emit('remove', item)"
                @move-up="$emit('move', index, -1)"
                @move-down="$emit('move', index, 1)"
            />
        </div>

        <div
            v-else
            class="rounded-2xl border border-dashed border-zinc-700 bg-zinc-950 p-10 text-center"
        >
            <p class="font-bold">
                Empty list
            </p>

            <p class="mt-2 text-sm text-zinc-500">
                Add games to start building your ranking.
            </p>
        </div>
    </div>
</template>