<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { ThumbsUp, X } from 'lucide-vue-next'

const props = defineProps({
    game: {
        type: Object,
        required: true,
    },

    note: {
        type: String,
        default: '',
    },

    rating: {
        type: String,
        default: '',
    },

    recommended: {
        type: Boolean,
        default: false,
    },

    notRecommended: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['close'])

const publicReviewTitle = ref(props.game.title)
const publicReviewBody = ref(props.note ?? '')
const publicReviewRating = ref(props.rating)
const publicReviewRecommended = ref(props.recommended)
const publicReviewNotRecommended = ref(props.notRecommended)

const togglePublicRecommended = () => {
    publicReviewRecommended.value = !publicReviewRecommended.value

    if (publicReviewRecommended.value) {
        publicReviewNotRecommended.value = false
    }
}

const togglePublicNotRecommended = () => {
    publicReviewNotRecommended.value = !publicReviewNotRecommended.value

    if (publicReviewNotRecommended.value) {
        publicReviewRecommended.value = false
    }
}

const blockInvalidKeys = (event) => {
    const allowedKeys = [
        'Backspace',
        'Delete',
        'ArrowLeft',
        'ArrowRight',
        'Tab',
    ]

    if (allowedKeys.includes(event.key)) {
        return
    }

    if (!/^\d$/.test(event.key)) {
        event.preventDefault()
    }
}

const normalizeRating = (value) => {
    let normalizedValue = value.replace(/\D/g, '').slice(0, 2)

    if (normalizedValue === '') {
        return ''
    }

    if (Number(normalizedValue) > 10) {
        return '10'
    }

    if (Number(normalizedValue) < 1) {
        return '1'
    }

    return normalizedValue
}

const handlePublicRatingInput = (event) => {
    publicReviewRating.value = normalizeRating(event.target.value)
}

const submitPublicReview = () => {
    router.post(
        '/reviews/public',
        {
            game_id: props.game.id,
            game_title: props.game.title,
            title: publicReviewTitle.value,
            body: publicReviewBody.value,
            rating: publicReviewRating.value
                ? Number(publicReviewRating.value)
                : null,
            recommended: publicReviewRecommended.value,
            not_recommended: publicReviewNotRecommended.value,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                emit('close')
            },
        }
    )
}
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6">
        <div class="w-full max-w-2xl rounded-3xl border border-zinc-800 bg-zinc-950 p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-white">
                        Create public review
                    </h2>

                    <p class="mt-1 text-sm text-zinc-400">
                        This review will be visible publicly.
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-900 hover:text-white"
                    @click="$emit('close')"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="mt-6 space-y-5">
                <div>
                    <label class="text-sm font-semibold text-zinc-300">
                        Review title
                    </label>

                    <input
                        v-model="publicReviewTitle"
                        type="text"
                        maxlength="120"
                        class="mt-2 w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                        placeholder="Short title"
                    />
                </div>

                <div>
                    <label class="text-sm font-semibold text-zinc-300">
                        Review
                    </label>

                    <textarea
                        v-model="publicReviewBody"
                        class="mt-2 min-h-48 w-full resize-none rounded-xl border border-zinc-800 bg-zinc-900 p-4 text-sm text-zinc-200 outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                        placeholder="Write your public review..."
                    />
                </div>

                <div class="grid gap-4 md:grid-cols-[160px_1fr]">
                    <div>
                        <label class="text-sm font-semibold text-zinc-300">
                            Rating
                        </label>

                        <div class="mt-2 flex items-center rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3">
                            <input
                                :value="publicReviewRating"
                                type="text"
                                inputmode="numeric"
                                maxlength="2"
                                placeholder="—"
                                class="w-10 bg-transparent text-sm font-bold text-white outline-none"
                                @keydown="blockInvalidKeys"
                                @input="handlePublicRatingInput"
                            />

                            <span class="text-sm font-semibold text-zinc-400">
                                /10
                            </span>
                        </div>
                    </div>

                    <div class="flex items-end gap-3">
                        <button
                            type="button"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-800 px-4 py-3 text-sm font-semibold transition"
                            :class="
                                publicReviewRecommended
                                    ? 'bg-emerald-500/10 text-emerald-300'
                                    : 'bg-zinc-900 text-zinc-300 hover:text-white'
                            "
                            @click="togglePublicRecommended"
                        >
                            <ThumbsUp class="h-4 w-4" />

                            {{
                                publicReviewRecommended
                                    ? 'Recommended'
                                    : 'Recommend'
                            }}
                        </button>

                        <button
                            type="button"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-800 px-4 py-3 text-sm font-semibold transition"
                            :class="
                                publicReviewNotRecommended
                                    ? 'bg-red-500/10 text-red-300'
                                    : 'bg-zinc-900 text-zinc-300 hover:text-white'
                            "
                            @click="togglePublicNotRecommended"
                        >
                            <X class="h-4 w-4" />

                            {{
                                publicReviewNotRecommended
                                    ? 'Not Recommended'
                                    : 'Do Not Recommend'
                            }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    class="rounded-xl border border-zinc-800 px-5 py-3 text-sm font-bold text-zinc-300 transition hover:bg-zinc-900 hover:text-white"
                    @click="$emit('close')"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                    @click="submitPublicReview"
                >
                    Publish review
                </button>
            </div>
        </div>
    </div>
</template>