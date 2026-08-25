<!-- eslint-disable vue/block-lang -->
<script setup>
import { router } from '@inertiajs/vue3';
import { ThumbsUp, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import RichTextEditor from '@/components/ui/RichTextEditor.vue';

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

    reviewTitle: {
        type: String,
        default: '',
    },

    platform: {
        type: String,
        default: '',
    },

    timeToBeatHours: {
        type: [String, Number],
        default: '',
    },

    editing: {
        type: Boolean,
        default: false,
    },

    reviewId: {
        type: [Number, String],
        default: null,
    },

    featuredOnProfile: {
        type: Boolean,
        default: false,
    },
    imageLayout: { type: String, default: 'grid' },
});

const emit = defineEmits(['close']);

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
];

const publicReviewTitle = ref(props.reviewTitle || props.game.title);
const publicReviewBody = ref(props.note ?? '');
const publicReviewRating = ref(props.rating);
const publicReviewRecommended = ref(props.recommended);
const publicReviewNotRecommended = ref(props.notRecommended);
const publicReviewPlatform = ref(props.platform);
const publicReviewScreenshot = ref(null);
const publicReviewImages = ref([]);
const imagePreviews = ref([]);
const publicReviewImageLayout = ref(props.imageLayout);
const publicReviewTimeToBeatHours = ref(String(props.timeToBeatHours ?? ''));

const reviewedGameId = computed(() => {
    if (props.game.is_custom && props.game.custom_game_id) {
        return `custom:${props.game.custom_game_id}`;
    }

    return props.game.database_id ?? props.game.id;
});

const canSubmit = computed(() => {
    return Boolean(
        publicReviewTitle.value.trim().length &&
        publicReviewBody.value.trim().length &&
        Number(publicReviewRating.value) >= 1 &&
        Number(publicReviewRating.value) <= 10,
    );
});

const togglePublicRecommended = () => {
    publicReviewRecommended.value = !publicReviewRecommended.value;

    if (publicReviewRecommended.value) {
        publicReviewNotRecommended.value = false;
    }
};

const togglePublicNotRecommended = () => {
    publicReviewNotRecommended.value = !publicReviewNotRecommended.value;

    if (publicReviewNotRecommended.value) {
        publicReviewRecommended.value = false;
    }
};

const blockInvalidKeys = (event) => {
    const allowedKeys = [
        'Backspace',
        'Delete',
        'ArrowLeft',
        'ArrowRight',
        'Tab',
    ];

    if (allowedKeys.includes(event.key)) {
        return;
    }

    if (!/^\d$/.test(event.key)) {
        event.preventDefault();
    }
};

const blockInvalidTimeKeys = (event) => {
    const allowedKeys = [
        'Backspace',
        'Delete',
        'ArrowLeft',
        'ArrowRight',
        'Tab',
        '.',
        ',',
    ];

    if (allowedKeys.includes(event.key)) {
        return;
    }

    if (!/^\d$/.test(event.key)) {
        event.preventDefault();
    }
};

const normalizeRating = (value) => {
    const normalizedValue = value.replace(/\D/g, '').slice(0, 2);

    if (normalizedValue === '') {
        return '';
    }

    if (Number(normalizedValue) > 10) {
        return '10';
    }

    if (Number(normalizedValue) < 1) {
        return '1';
    }

    return normalizedValue;
};

const normalizeTimeToBeat = (value) => {
    return String(value ?? '')
        .replace(',', '.')
        .replace(/[^\d.]/g, '')
        .replace(/(\..*)\./g, '$1')
        .slice(0, 7);
};

const handlePublicRatingInput = (event) => {
    publicReviewRating.value = normalizeRating(event.target.value);
};

const handleTimeToBeatInput = (event) => {
    publicReviewTimeToBeatHours.value = normalizeTimeToBeat(event.target.value);
};

const handleScreenshotInput = (event) => {
    publicReviewScreenshot.value = event.target.files[0] ?? null;
};

const handleImagesInput = (event) => {
    imagePreviews.value.forEach((url) => URL.revokeObjectURL(url));
    publicReviewImages.value = Array.from(event.target.files ?? []).slice(
        0,
        10,
    );
    imagePreviews.value = publicReviewImages.value.map((file) =>
        URL.createObjectURL(file),
    );
};

const moveImage = (index, direction) => {
    const target = index + direction;

    if (target < 0 || target >= publicReviewImages.value.length) {
        return;
    }

    [publicReviewImages.value[index], publicReviewImages.value[target]] = [
        publicReviewImages.value[target],
        publicReviewImages.value[index],
    ];
    [imagePreviews.value[index], imagePreviews.value[target]] = [
        imagePreviews.value[target],
        imagePreviews.value[index],
    ];
    publicReviewImages.value = [...publicReviewImages.value];
    imagePreviews.value = [...imagePreviews.value];
};

const submitPublicReview = () => {
    const url =
        props.editing && props.reviewId
            ? `/reviews/${props.reviewId}`
            : '/reviews/public';

    router.post(
        url,
        {
            ...(props.editing ? { _method: 'patch' } : {}),
            game_id: reviewedGameId.value,
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
            images: publicReviewImages.value,
            image_layout: publicReviewImageLayout.value,
            time_to_beat_hours: publicReviewTimeToBeatHours.value || null,
            recommended: publicReviewRecommended.value,
            not_recommended: publicReviewNotRecommended.value,
            is_featured_on_profile: props.featuredOnProfile,
        },
        {
            preserveScroll: true,
            forceFormData: true,

            onSuccess: () => {
                emit('close');
            },
        },
    );
};
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-end justify-center bg-black/70 sm:items-center sm:p-6"
    >
        <div
            class="flex h-[min(94dvh,760px)] w-full max-w-[960px] flex-col overflow-hidden rounded-t-3xl border border-zinc-800 bg-zinc-950 shadow-2xl sm:h-[min(90vh,720px)] sm:rounded-3xl lg:resize"
        >
            <div
                class="flex shrink-0 items-start justify-between gap-4 border-b border-zinc-800 px-4 py-4 sm:px-8 sm:py-6"
            >
                <div>
                    <h2 class="text-xl font-black text-white sm:text-2xl">
                        {{
                            editing
                                ? 'Edit public review'
                                : 'Create public review'
                        }}
                    </h2>

                    <p class="mt-1 text-sm text-zinc-400">
                        {{
                            editing
                                ? 'Update the review visible on your profile and public link.'
                                : 'This review will be visible publicly.'
                        }}
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

            <div
                class="review-modal-scroll min-h-0 flex-1 space-y-4 overflow-y-auto px-4 py-4 sm:space-y-5 sm:px-8 sm:py-6"
            >
                <div>
                    <label class="text-sm font-semibold text-zinc-300">
                        Review title
                    </label>

                    <input
                        v-model="publicReviewTitle"
                        type="text"
                        maxlength="120"
                        required
                        class="mt-1.5 w-full rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2.5 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600 sm:mt-2 sm:px-4 sm:py-3"
                        placeholder="Short title"
                    />
                </div>

                <div>
                    <label class="text-sm font-semibold text-zinc-300">
                        Review
                    </label>

                    <RichTextEditor
                        v-model="publicReviewBody"
                        :maxlength="5000"
                        :rows="9"
                        class="mt-2"
                        placeholder="Write your public review..."
                    />
                </div>

                <div
                    class="grid grid-cols-[100px_minmax(0,1fr)] gap-3 sm:grid-cols-[160px_minmax(0,1fr)] sm:gap-4"
                >
                    <div>
                        <label class="text-sm font-semibold text-zinc-300">
                            Rating
                        </label>

                        <div
                            class="mt-1.5 flex items-center rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2.5 sm:mt-2 sm:px-4 sm:py-3"
                        >
                            <input
                                :value="publicReviewRating"
                                type="text"
                                inputmode="numeric"
                                maxlength="2"
                                required
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
                            class="mt-1.5 w-full rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2.5 text-sm text-white outline-none focus:border-zinc-600 sm:mt-2 sm:px-4 sm:py-3"
                        >
                            <option value="">Select platform</option>

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

                    <div
                        class="mt-1.5 flex items-center rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2.5 sm:mt-2 sm:px-4 sm:py-3"
                    >
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
                    <div class="flex items-center justify-between gap-3">
                        <label class="text-sm font-semibold text-zinc-300"
                            >Gallery images</label
                        >
                        <select
                            v-model="publicReviewImageLayout"
                            class="rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-xs text-white"
                        >
                            <option value="grid">Grid</option>
                            <option value="carousel">Carousel</option>
                            <option value="full">Full width</option>
                        </select>
                    </div>
                    <input
                        type="file"
                        multiple
                        accept="image/jpeg,image/png,image/webp"
                        class="mt-2 w-full rounded-xl border border-zinc-800 bg-zinc-900 p-3 text-sm text-zinc-300"
                        @input="handleImagesInput"
                    />
                    <p class="mt-2 text-xs text-zinc-500">
                        Up to 10 images. Use arrows to arrange their order.
                    </p>
                    <div
                        v-if="imagePreviews.length"
                        class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4"
                    >
                        <div
                            v-for="(preview, index) in imagePreviews"
                            :key="preview"
                            class="overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900"
                        >
                            <img
                                :src="preview"
                                alt=""
                                class="aspect-video w-full object-cover"
                            />
                            <div
                                class="grid grid-cols-2 border-t border-zinc-800 text-xs"
                            >
                                <button
                                    type="button"
                                    class="p-2 hover:bg-zinc-800"
                                    @click="moveImage(index, -1)"
                                >
                                    ←</button
                                ><button
                                    type="button"
                                    class="p-2 hover:bg-zinc-800"
                                    @click="moveImage(index, 1)"
                                >
                                    →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-zinc-300">
                        Screenshot
                    </label>

                    <input
                        type="file"
                        accept="image/*"
                        class="mt-1.5 w-full min-w-0 rounded-xl border border-zinc-800 bg-zinc-900 px-2 py-2 text-xs text-zinc-300 file:mr-2 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:text-xs file:font-bold file:text-zinc-950 sm:mt-2 sm:px-4 sm:py-3 sm:text-sm sm:file:mr-4 sm:file:px-4 sm:file:text-sm"
                        @input="handleScreenshotInput"
                    />

                    <p class="mt-2 text-xs text-zinc-500">
                        Upload exactly one screenshot. Supported: JPG, PNG,
                        WEBP.
                    </p>
                </div>

                <div class="grid gap-2 sm:grid-cols-2 sm:gap-3">
                    <button
                        type="button"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-800 px-3 py-2.5 text-xs font-semibold transition sm:px-4 sm:py-3 sm:text-sm"
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
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-800 px-3 py-2.5 text-xs font-semibold transition sm:px-4 sm:py-3 sm:text-sm"
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

            <div
                class="grid shrink-0 grid-cols-2 gap-2 border-t border-zinc-800 bg-zinc-950 px-4 pt-3 pb-[calc(1rem+env(safe-area-inset-bottom))] sm:flex sm:justify-end sm:gap-3 sm:px-8 sm:py-5"
            >
                <button
                    type="button"
                    class="rounded-xl border border-zinc-800 px-3 py-3 text-sm font-bold text-zinc-300 transition hover:bg-zinc-900 hover:text-white sm:px-5"
                    @click="$emit('close')"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="rounded-xl bg-white px-3 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200 sm:px-5"
                    :disabled="!canSubmit"
                    @click="submitPublicReview"
                >
                    {{ editing ? 'Save changes' : 'Publish review' }}
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
