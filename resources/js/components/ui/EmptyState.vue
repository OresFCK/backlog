<script setup>
import { Library } from 'lucide-vue-next'

defineProps({
    title: {
        type: String,
        default: 'Nothing here yet',
    },
    description: {
        type: String,
        default: 'Start by adding your first game.',
    },
    icon: {
        type: Object,
        default: Library,
    },
})
</script>

<template>
    <div
        class="flex min-h-[360px] flex-col items-center justify-center rounded-3xl border border-dashed border-zinc-800 bg-zinc-900/40 p-10 text-center"
    >
        <div
            class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-800 text-zinc-400"
        >
            <component :is="icon" class="h-8 w-8" />
        </div>

        <h3 class="text-xl font-semibold text-white">
            {{ title }}
        </h3>

        <p class="mt-2 max-w-md text-sm text-zinc-400">
            {{ description }}
        </p>

        <div class="mt-6">
            <slot />
        </div>
    </div>
</template>