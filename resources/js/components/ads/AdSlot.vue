<script setup>
defineProps({
    label: { type: String, default: 'Advertisement' },
    format: { type: String, default: 'leaderboard' },
});
</script>

<template>
    <aside
        class="flex items-center justify-center rounded-2xl border border-dashed border-zinc-800 bg-zinc-900/40 text-center text-[10px] font-bold tracking-[0.22em] text-zinc-600 uppercase"
        :class="format === 'sidebar' ? 'min-h-64' : 'min-h-24'"
        :aria-label="label"
    >
        {{ label }}
    </aside>
</template>
