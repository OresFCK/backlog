<template>
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="visible"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-zinc-950/90 backdrop-blur-sm"
        >
            <div class="flex flex-col items-center gap-4">
                <div
                    class="h-12 w-12 animate-spin rounded-full border-4 border-zinc-700 border-t-white"
                />

                <p class="text-sm font-semibold text-zinc-300">
                    Loading...
                </p>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref } from 'vue'
defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
})
</script>