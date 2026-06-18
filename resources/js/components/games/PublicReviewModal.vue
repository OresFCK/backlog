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

const platforms = [
    { value: 'pc', label: 'PC' },
    { value: 'steam_deck', label: 'Steam Deck' },
    { value: 'playstation_5', label: 'PlayStation 5' },
    { value: 'playstation_4', label: 'PlayStation 4' },
    { value: 'xbox_series', label: 'Xbox Series X/S' },
    { value: 'xbox_one', label: 'Xbox One' },
    { value: 'nintendo_switch', label: 'Nintendo Switch' },
    { value: 'nintendo_switch_2', label: 'Nintendo Switch 2' },
    { value: 'ios', label: 'iOS' },
    { value: 'android', label: 'Android' },
    { value: 'other', label: 'Other' },
]

const publicReviewTitle = ref(props.game.title)
const publicReviewBody = ref(props.note ?? '')
const publicReviewRating = ref(props.rating)
const publicReviewRecommended = ref(props.recommended)
const publicReviewNotRecommended = ref(props.notRecommended)
const publicReviewPlatform = ref('')
const publicReviewScreenshot = ref(null)
const publicReviewTimeToBeatHours = ref('')

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

const blockInvalidTimeKeys = (event) => {
    const allowedKeys = [
        'Backspace',
        'Delete',
        'ArrowLeft',
        'ArrowRight',
        'Tab',
        '.',
        ',',
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

const normalizeTimeToBeat = (value) => {
    return String(value ?? '')
        .replace(',', '.')
        .replace(/[^\d.]/g, '')
        .replace(/(\..*)\./g, '$1')
        .slice(0, 7)
}

const handlePublicRatingInput = (event) => {
    publicReviewRating.value = normalizeRating(event.target.value)
}

const handleTimeToBeatInput = (event) => {
    publicReviewTimeToBeatHours.value = normalizeTimeToBeat(event.target.value)
}

const handleScreenshotInput = (event) => {
    publicReviewScreenshot.value = event.target.files[0] ?? null
}

const submitPublicReview = () => {
    router.post(
        '/reviews/public',
        {
            game_id: props.game.database_id || props.game.id,
            source: props.game.source || null,
            source_game_id:
                props.game.appid ||
                props.game.steam_app_id ||
                props.game.igdb_id ||
                props.game.id,

            game_title: props.game.title,
            title: publicReviewTitle.value,
            body: publicReviewBody.value,
            rating: publicReviewRating.value
                ? Number(publicReviewRating.value)
                : null,
            platform: publicReviewPlatform.value,
            screenshot: publicReviewScreenshot.value,
            time_to_beat_hours: publicReviewTimeToBeatHours.value || null,
            recommended: publicReviewRecommended.value,
            not_recommended: publicReviewNotRecommended.value,
        },
        {
            preserveScroll: true,
            forceFormData: true,

            onSuccess: () => {
                emit('close')
            },
        }
    )
}
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6">
        <div
            class="review-modal-scroll h-[720px] w-[960px] max-w-[95vw] resize overflow-auto rounded-3xl border border-zinc-800 bg-zinc-950 p-8 shadow-2xl"
            style="
                min-width: 420px;
                min-height: 360px;
                max-height: 95vh;
            "
        >
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

                    <div>
                        <label class="text-sm font-semibold text-zinc-300">
                            Platform
                        </label>

                        <select
                            v-model="publicReviewPlatform"
                            class="mt-2 w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-600"
                        >
                            <option value="">
                                Select platform
                            </option>

                            <option
                                v-for="platform in platforms"
                                :key="platform.value"
                                :value="platform.value"
                            >
                                {{ platform.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-zinc-300">
                        Time till beaten
                    </label>

                    <div class="mt-2 flex items-center rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3">
                        <input
                            :value="publicReviewTimeToBeatHours"
                            type="text"
                            inputmode="decimal"
                            placeholder="e.g. 24.5"
                            class="w-full bg-transparent text-sm text-white outline-none placeholder:text-zinc-500"
                            @keydown="blockInvalidTimeKeys"
                            @input="handleTimeToBeatInput"
                        />

                        <span class="ml-3 text-sm font-semibold text-zinc-400">
                            hours
                        </span>
                    </div>

                    <p class="mt-2 text-xs text-zinc-500">
                        Stored for future features. Not displayed yet.
                    </p>
                </div>

                <div>
                    <label class="text-sm font-semibold text-zinc-300">
                        Screenshot
                    </label>

                    <input
                        type="file"
                        accept="image/*"
                        class="mt-2 w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-sm text-zinc-300 file:mr-4 file:rounded-lg file:border-0 file:bg-white file:px-4 file:py-2 file:text-sm file:font-bold file:text-zinc-950"
                        @input="handleScreenshotInput"
                    />

                    <p class="mt-2 text-xs text-zinc-500">
                        Upload exactly one screenshot. Supported: JPG, PNG, WEBP.
                    </p>
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

<style scoped>
.review-modal-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgb(82 82 91) transparent;
}

.review-modal-scroll::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

.review-modal-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.review-modal-scroll::-webkit-scrollbar-thumb {
    background: rgb(63 63 70);
    border-radius: 999px;
    border: 3px solid rgb(9 9 11);
}

.review-modal-scroll::-webkit-scrollbar-thumb:hover {
    background: rgb(113 113 122);
}

.review-modal-scroll::-webkit-resizer {
    background: rgb(9 9 11);
}
</style>