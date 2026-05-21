<script setup>
defineProps({
    status: {
        type: String,
        required: true,
    },

    color: {
        type: String,
        default: '#71717a',
    },
})
</script>

<template>
    <span
        class="rounded-full border px-2 py-1 text-xs font-medium"
        :style="{
            backgroundColor: `${color}20`,
            borderColor: `${color}40`,
            color,
        }"
    >
        {{ status }}
    </span>
</template>