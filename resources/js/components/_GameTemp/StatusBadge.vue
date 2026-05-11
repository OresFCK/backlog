<script setup>
import { computed } from 'vue'

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
})

const styles = computed(() => {
    switch (props.status) {
        case 'playing':
            return 'bg-blue-500/10 text-blue-400 border-blue-500/20'

        case 'finished':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'

        case 'dropped':
            return 'bg-red-500/10 text-red-400 border-red-500/20'

        case 'wishlist':
            return 'bg-pink-500/10 text-pink-400 border-pink-500/20'

        default:
            return 'bg-zinc-800 text-zinc-300 border-zinc-700'
    }
})
</script>

<template>
    <span
        class="rounded-full border px-2 py-1 text-xs font-medium capitalize"
        :class="styles"
    >
        {{ status }}
    </span>
</template>