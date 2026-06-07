<script setup>
import { computed } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
    review: {
        type: Object,
        required: true,
    },
})

defineEmits(['close'])

const formattedBody = computed(() => {
    return String(props.review.body ?? '')
        .replace(/\r\n/g, '\n')
        .replace(/\n{3,}/g, '\n\n')
        .trim()
})
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6"
        @click.self="$emit('close')"
    >
        <div
            class="flex max-h-[85vh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-950"
        >
            <div
                class="flex shrink-0 items-start justify-between gap-4 border-b border-zinc-800 p-6"
            >
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

            <div
                class="min-h-0 overflow-y-auto p-6"
            >
                <div
                    class="prose prose-invert max-w-none"
                >
                    <div
                        class="whitespace-pre-wrap break-words text-[15px] leading-7 text-zinc-300"
                    >
                        {{ formattedBody }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>