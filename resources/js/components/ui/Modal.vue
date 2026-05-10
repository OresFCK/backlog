<script setup>
import { X } from 'lucide-vue-next'

defineProps({
    show: {
        type: Boolean,
        required: true,
    },
    title: {
        type: String,
        default: '',
    },
    description: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['close'])
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div
                class="absolute inset-0 bg-black/70 backdrop-blur-sm"
                @click="emit('close')"
            />

            <div
                class="relative z-10 w-full max-w-lg rounded-3xl border border-zinc-800 bg-zinc-950 p-6 shadow-2xl"
            >
                <div class="mb-6 flex items-start justify-between gap-6">
                    <div>
                        <h2
                            v-if="title"
                            class="text-xl font-bold text-white"
                        >
                            {{ title }}
                        </h2>

                        <p
                            v-if="description"
                            class="mt-1 text-sm text-zinc-400"
                        >
                            {{ description }}
                        </p>
                    </div>

                    <button
                        class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-900 hover:text-white"
                        @click="emit('close')"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <slot />
            </div>
        </div>
    </Teleport>
</template>