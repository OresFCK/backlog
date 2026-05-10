<script setup>
const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
    },
    size: {
        type: String,
        default: 'md',
    },
    type: {
        type: String,
        default: 'button',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
})
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex items-center justify-center rounded-xl font-medium transition disabled:cursor-not-allowed disabled:opacity-50"
        :class="[
            size === 'sm' && 'px-3 py-2 text-xs',
            size === 'md' && 'px-5 py-3 text-sm',
            size === 'lg' && 'px-6 py-4 text-base',

            variant === 'primary' && 'bg-white text-black hover:bg-zinc-200',
            variant === 'secondary' && 'border border-zinc-700 bg-zinc-900 text-white hover:bg-zinc-800',
            variant === 'ghost' && 'text-zinc-400 hover:bg-zinc-900 hover:text-white',
            variant === 'danger' && 'bg-red-500 text-white hover:bg-red-600',
        ]"
    >
        <slot />
    </button>
</template>