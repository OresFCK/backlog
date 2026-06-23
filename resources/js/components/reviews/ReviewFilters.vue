<script setup>
defineProps({
    platforms: {
        type: Array,
        default: () => [],
    },

    platform: {
        type: String,
        default: 'all',
    },
})

defineEmits(['update:platform'])
</script>

<template>
    <nav
        v-if="platforms.length"
        class="mt-6 flex flex-wrap gap-2"
        aria-label="Filter reviews by platform"
    >
        <button
            type="button"
            class="rounded-xl border px-4 py-2 text-sm font-bold transition"
            :class="platform === 'all'
                ? 'border-white bg-white text-zinc-950'
                : 'border-zinc-700 text-zinc-300 hover:bg-zinc-800 hover:text-white'"
            @click="$emit('update:platform', 'all')"
        >
            All
        </button>

        <button
            v-for="item in platforms"
            :key="item.value"
            type="button"
            class="rounded-xl border px-4 py-2 text-sm font-bold transition"
            :class="platform === item.value
                ? 'border-white bg-white text-zinc-950'
                : 'border-zinc-700 text-zinc-300 hover:bg-zinc-800 hover:text-white'"
            @click="$emit('update:platform', item.value)"
        >
            {{ item.label }} · {{ item.count }}
        </button>
    </nav>
</template>