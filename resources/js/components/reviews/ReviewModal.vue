<script setup>
import { X } from 'lucide-vue-next'

defineProps({
    review: {
        type: Object,
        required: true,
    },
})

defineEmits(['close'])
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6">
        <div class="w-full max-w-3xl rounded-3xl border border-zinc-800 bg-zinc-950 p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-indigo-300">
                        {{ review.game_title }}
                    </p>

                    <h2 class="mt-1 text-2xl font-black text-white">
                        {{ review.title || 'Untitled review' }}
                    </h2>
                </div>

                <button
                    type="button"
                    class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-900 hover:text-white"
                    @click="$emit('close')"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <p class="mt-6 whitespace-pre-line text-zinc-300">
                {{ review.body }}
            </p>
        </div>
    </div>
</template>