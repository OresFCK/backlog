<!-- eslint-disable vue/block-lang -->
<script setup>
import { Flag, ImagePlus, Pencil, Share2, Star } from 'lucide-vue-next';
import { ref } from 'vue';
import ReviewGraphicModal from '@/components/reviews/ReviewGraphicModal.vue';
import RichTextContent from '@/components/ui/RichTextContent.vue';

const props = defineProps({
    review: {
        type: Object,
        required: true,
    },
    allowEdit: {
        type: Boolean,
        default: false,
    },
    expandInline: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    'read-more',
    'toggle-vote',
    'toggle-featured',
    'report-review',
    'edit-review',
]);

const isExpanded = ref(false);
const graphicCreatorOpen = ref(false);

const handleReadMore = () => {
    if (props.expandInline) {
        isExpanded.value = !isExpanded.value;

        return;
    }

    emit('read-more', props.review);
};

const shareReview = async (review) => {
    const shareData = {
        title: `${review.title || 'Game review'} — ${review.game_title}`,
        text: `${review.user?.name || 'A Curator.gg user'} reviewed ${review.game_title}${review.rating ? ` ${review.rating}/10` : ''}.`,
        url: review.share_url,
    };

    if (navigator.share) {
        try {
            await navigator.share(shareData);

            return;
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
        }
    }

    await navigator.clipboard.writeText(review.share_url);
    window.alert('Review link copied to clipboard.');
};

const platformLabel = (platform) => {
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
    };

    return labels[platform] ?? platform;
};

const shouldTruncate = (body) => {
    return String(body ?? '').length > 420;
};

const truncatedBody = (body) => {
    const text = String(body ?? '');

    if (text.length <= 420) {
        return text;
    }

    return `${text.slice(0, 420)}...`;
};
</script>

<template>
    <article
        class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4 sm:rounded-3xl sm:p-6"
    >
        <div class="flex items-start gap-3 sm:gap-4">
            <img
                v-if="review.user?.avatar"
                :src="review.user.avatar"
                class="h-10 w-10 shrink-0 rounded-xl object-cover sm:h-14 sm:w-14 sm:rounded-2xl"
            />

            <div
                v-else
                class="h-10 w-10 shrink-0 rounded-xl bg-zinc-800 sm:h-14 sm:w-14 sm:rounded-2xl"
            />

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-base font-bold text-white sm:text-lg">
                        {{ review.user?.name ?? 'Unknown user' }}
                    </h2>

                    <span class="text-sm text-zinc-500">
                        {{ review.created_at }}
                    </span>

                    <span
                        v-if="review.rating"
                        class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-1 text-sm font-bold text-white"
                    >
                        {{ review.rating }}/10
                    </span>

                    <span
                        v-if="review.platform"
                        class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-1 text-sm font-bold text-zinc-300"
                    >
                        {{ platformLabel(review.platform) }}
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

                <p class="mt-4 text-sm font-bold text-indigo-300">
                    {{ review.game_title || 'Unknown game' }}
                </p>

                <h3
                    class="mt-1 text-xl font-black break-words text-white sm:text-2xl"
                >
                    {{ review.title || 'Untitled review' }}
                </h3>

                <a
                    v-if="review.screenshot_url"
                    :href="review.screenshot_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-4 flex max-h-[420px] items-center justify-center overflow-hidden rounded-2xl border border-zinc-800 bg-black/40 p-2 sm:p-3"
                >
                    <img
                        :src="review.screenshot_url"
                        :alt="review.title || review.game_title"
                        class="block h-auto max-h-[396px] w-auto max-w-full object-contain"
                    />
                </a>

                <RichTextContent
                    :content="
                        expandInline && isExpanded
                            ? review.body
                            : truncatedBody(review.body)
                    "
                    class="mt-3 text-sm leading-6 break-words whitespace-pre-line text-zinc-300 sm:mt-4 sm:text-base"
                />

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <template v-if="review.can_vote">
                        <button
                            type="button"
                            class="rounded-xl border px-3 py-1 text-sm font-bold transition"
                            :class="
                                review.user_vote === 1
                                    ? 'border-emerald-500 bg-emerald-500/10 text-emerald-300'
                                    : 'border-zinc-700 bg-zinc-950 text-zinc-300 hover:text-white'
                            "
                            @click="$emit('toggle-vote', review, 1)"
                        >
                            +1
                        </button>

                        <button
                            type="button"
                            class="rounded-xl border px-3 py-1 text-sm font-bold transition"
                            :class="
                                review.user_vote === -1
                                    ? 'border-red-500 bg-red-500/10 text-red-300'
                                    : 'border-zinc-700 bg-zinc-950 text-zinc-300 hover:text-white'
                            "
                            @click="$emit('toggle-vote', review, -1)"
                        >
                            -1
                        </button>
                    </template>

                    <span class="text-sm font-bold text-zinc-400">
                        Score: {{ review.votes_score ?? 0 }}
                    </span>

                    <button
                        v-if="review.share_url"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-1 text-sm font-bold text-zinc-300 transition hover:border-indigo-500/60 hover:text-white"
                        @click="shareReview(review)"
                    >
                        <Share2 class="h-4 w-4" />
                        Share
                    </button>

                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-1 text-sm font-bold text-zinc-300 transition hover:border-indigo-500/60 hover:text-white"
                        @click="graphicCreatorOpen = true"
                    >
                        <ImagePlus class="h-4 w-4" />
                        Create graphic
                    </button>

                    <button
                        v-if="allowEdit && review.is_owner"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-1 text-sm font-bold text-zinc-300 transition hover:border-indigo-500/60 hover:text-white"
                        @click="$emit('edit-review', review)"
                    >
                        <Pencil class="h-4 w-4" />
                        Edit review
                    </button>

                    <button
                        v-if="review.is_owner"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border px-3 py-1 text-sm font-bold transition"
                        :class="
                            review.is_featured_on_profile
                                ? 'border-indigo-500/40 bg-indigo-500/10 text-indigo-300'
                                : 'border-zinc-700 bg-zinc-950 text-zinc-300 hover:text-white'
                        "
                        @click="$emit('toggle-featured', review)"
                    >
                        <Star class="h-4 w-4" />

                        {{
                            review.is_featured_on_profile
                                ? 'Featured on Profile'
                                : 'Feature on Profile'
                        }}
                    </button>

                    <button
                        v-if="!review.is_owner"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-1 text-sm font-bold text-zinc-300 transition hover:border-red-500/60 hover:text-red-300"
                        @click="$emit('report-review', review)"
                    >
                        <Flag class="h-4 w-4" />
                        Report
                    </button>
                </div>

                <button
                    v-if="shouldTruncate(review.body)"
                    type="button"
                    class="mt-4 text-sm font-bold text-white underline underline-offset-4 transition hover:text-zinc-300"
                    @click="handleReadMore"
                >
                    {{ expandInline && isExpanded ? 'Show less' : 'Read more' }}
                </button>
            </div>
        </div>
        <ReviewGraphicModal
            :open="graphicCreatorOpen"
            :review="review"
            :image-url="review.graphic_image_url"
            @close="graphicCreatorOpen = false"
        />
    </article>
</template>
