<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    item: Object,
    index: Number,
    isFirst: Boolean,
    isLast: Boolean,
    isDragging: Boolean,
})

defineEmits([
    'remove',
    'move-up',
    'move-down',
    'dragstart',
    'dragover',
    'drop',
    'dragend',
])
</script>

<template>
    <div
        class="flex cursor-grab gap-4 rounded-2xl border border-zinc-800 bg-zinc-950 p-4 transition active:cursor-grabbing"
        :class="{
            'opacity-40 ring-2 ring-zinc-600':
                isDragging,
        }"
        draggable="true"
        @dragstart="$emit('dragstart', $event)"
        @dragover="$emit('dragover', $event)"
        @drop="$emit('drop', $event)"
        @dragend="$emit('dragend', $event)"
    >
        <div
            class="flex w-10 shrink-0 flex-col items-center justify-center"
        >
            <span class="text-2xl font-black text-zinc-600">
                #{{ index + 1 }}
            </span>

            <span
                class="mt-1 text-xs text-zinc-600"
            >
                ⋮⋮
            </span>
        </div>

        <img
            v-if="item.game_cover_url"
            :src="item.game_cover_url"
            :alt="item.game_title"
            class="h-28 w-20 shrink-0 rounded-xl object-cover"
        />

        <div class="min-w-0 flex-1">
            <h3 class="truncate font-black text-white">
                {{ item.game_title }}
            </h3>

            <Link
                :href="`/games/${item.game_id}`"
                class="mt-2 inline-flex rounded-lg border border-blue-700 px-3 py-1 text-xs font-bold text-blue-400 transition hover:bg-blue-950/40"
            >
                Check in library
            </Link>

            <p
                v-if="item.note"
                class="mt-3 line-clamp-3 text-sm text-zinc-400"
            >
                {{ item.note }}
            </p>

            <p
                v-else
                class="mt-3 text-sm text-zinc-600"
            >
                No note.
            </p>
        </div>

        <div class="flex shrink-0 flex-col gap-2">
            <button
                type="button"
                class="rounded-lg border border-zinc-700 px-3 py-2 text-sm font-bold text-white transition hover:bg-zinc-800 disabled:opacity-40"
                :disabled="isFirst"
                @click="$emit('move-up')"
            >
                ↑
            </button>

            <button
                type="button"
                class="rounded-lg border border-zinc-700 px-3 py-2 text-sm font-bold text-white transition hover:bg-zinc-800 disabled:opacity-40"
                :disabled="isLast"
                @click="$emit('move-down')"
            >
                ↓
            </button>

            <button
                type="button"
                class="rounded-lg border border-red-900/60 px-3 py-2 text-sm font-bold text-red-400 transition hover:bg-red-950/40"
                @click="$emit('remove')"
            >
                Remove
            </button>
        </div>
    </div>
</template>