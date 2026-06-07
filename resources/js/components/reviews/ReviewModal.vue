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

const platformLabel = computed(() => {
    const labels = {
        pc: 'PC',
        steam_deck: 'Steam Deck',
        playstation_5: 'PlayStation 5',
        playstation_4: 'PlayStation 4',
        xbox_series: 'Xbox Series X/S',
        xbox_one: 'Xbox One',
        nintendo_switch: 'Nintendo Switch',
        nintendo_switch_2: 'Nintendo Switch 2',
        ios: 'iOS',
        android: 'Android',
        other: 'Other',
    }

    return labels[props.review.platform] ?? props.review.platform
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

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span
                            v-if="review.rating"
                            class="rounded-xl border border-zinc-700 bg-zinc-900 px-3 py-1 text-sm font-bold text-white"
                        >
                            {{ review.rating }}/10
                        </span>

                        <span
                            v-if="review.platform"
                            class="rounded-xl border border-zinc-700 bg-zinc-900 px-3 py-1 text-sm font-bold text-zinc-300"
                        >
                            {{ platformLabel }}
                        </span>

                        <span
                            v-if="review.recommended"
                            class="rounded-xl bg-emerald-500/10 px-3 py-1 text-sm font-bold text-emerald-300"
                        >
                            Recommended
                        </span>

                        <span
                            v-if="review.not_recommended"
                            class="rounded-xl bg-red-500/10 px-3 py-1 text-sm font-bold text-red-300"
                        >
                            Not Recommended
                        </span>
                    </div>
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
                <a
                    v-if="review.screenshot_url"
                    :href="review.screenshot_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mb-6 block overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900"
                >
                    <img
                        :src="review.screenshot_url"
                        :alt="review.title || review.game_title"
                        class="max-h-[520px] w-full object-contain"
                    >
                </a>

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