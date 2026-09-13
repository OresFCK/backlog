<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },

    color: {
        type: String,
        default: '#71717a',
    },
});

const isUnsorted = computed(() => {
    return (
        !props.status ||
        ['no status', 'unsorted'].includes(props.status.toLowerCase())
    );
});

const label = computed(() => (isUnsorted.value ? 'Unsorted' : props.status));
</script>

<template>
    <span
        class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full border px-2.5 py-1 text-[11px] font-semibold leading-4"
        :class="
            isUnsorted
                ? 'border-zinc-700/80 bg-zinc-800/70 text-zinc-400'
                : ''
        "
        :style="
            isUnsorted
                ? undefined
                : {
                      backgroundColor: `${color}20`,
                      borderColor: `${color}40`,
                      color,
                  }
        "
    >
        <span
            v-if="isUnsorted"
            class="h-1.5 w-1.5 rounded-full bg-zinc-500"
            aria-hidden="true"
        />
        {{ label }}
    </span>
</template>
