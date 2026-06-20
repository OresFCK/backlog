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
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-6"
        @click.self="$emit('close')"
    >
        <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-3xl border border-zinc-800 bg-zinc-950 shadow-2xl">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-800 bg-zinc-950/95 p-5 backdrop-blur">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-purple-300">
                        Review
                    </p>

                    <h2 class="mt-1 text-2xl font-black">
                        {{ review.title }}
                    </h2>
                </div>

                <button
                    type="button"
                    class="rounded-xl bg-zinc-800 p-2 text-zinc-300 transition hover:bg-zinc-700 hover:text-white"
                    @click="$emit('close')"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="p-6">
                <div class="mb-5 flex items-center gap-3">
                    <img
                        v-if="review.user?.avatar"
                        :src="review.user.avatar"
                        :alt="review.user.name"
                        class="h-11 w-11 rounded-xl object-cover"
                    />

                    <div
                        v-else
                        class="h-11 w-11 rounded-xl bg-zinc-800"
                    />

                    <div>
                        <p class="font-bold">
                            {{ review.user?.name }}
                        </p>

                        <p class="text-xs text-zinc-500">
                            {{ review.created_at_human }}
                        </p>
                    </div>
                </div>

                <p
                    v-if="review.game_title"
                    class="text-sm font-bold text-indigo-300"
                >
                    {{ review.game_title }}
                </p>

                <img
                    v-if="review.screenshot_url"
                    :src="review.screenshot_url"
                    :alt="review.title"
                    class="mt-5 max-h-[520px] w-full rounded-2xl border border-zinc-800 object-cover"
                />

                <p
                    v-if="review.body"
                    class="mt-6 whitespace-pre-line text-sm leading-7 text-zinc-200"
                >
                    {{ review.body }}
                </p>
            </div>
        </div>
    </div>
</template>