<!-- eslint-disable vue/block-lang -->
<script setup>
import { router } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    BookOpen,
    Flag,
    List,
    Package,
    Star,
    ThumbsUp,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';
import RichTextContent from '@/components/ui/RichTextContent.vue';

const props = defineProps({
    profileUser: Object,

    featuredGames: {
        type: Array,
        default: () => [],
    },

    featuredReviews: {
        type: Array,
        default: () => [],
    },

    featuredWardrobeItems: {
        type: Array,
        default: () => [],
    },

    publicCustomLists: {
        type: Array,
        default: () => [],
    },
    publicReviews: { type: Array, default: () => [] },
    publicBlogPosts: { type: Array, default: () => [] },
    viewerAuthenticated: { type: Boolean, default: false },
    isOwnProfile: { type: Boolean, default: false },
});

const selectedList = ref(null);

function openList(list) {
    selectedList.value = list;
}

function closeList() {
    selectedList.value = null;
}

const requireLogin = () => {
    window.location.assign(
        `/auth/steam?intended=${encodeURIComponent(window.location.pathname)}`,
    );
};

const voteReview = (review, value) => {
    if (!props.viewerAuthenticated) {
        return requireLogin();
    }

    if (!review.can_interact) {
        return;
    }

    if (review.user_vote === value) {
        router.delete(`/reviews/${review.id}/vote`, { preserveScroll: true });
    } else {
        router.post(
            `/reviews/${review.id}/vote`,
            { value },
            { preserveScroll: true },
        );
    }
};

const votePost = (post, value) => {
    if (!props.viewerAuthenticated) {
        return requireLogin();
    }

    if (!post.can_interact) {
        return;
    }

    if (post.user_vote === value) {
        router.delete(`/blog/${post.slug}/vote`, { preserveScroll: true });
    } else {
        router.post(
            `/blog/${post.slug}/vote`,
            { value },
            { preserveScroll: true },
        );
    }
};

const reportContent = (type, item) => {
    if (!props.viewerAuthenticated) {
        return requireLogin();
    }

    if (!item.can_interact) {
        return;
    }

    const reason = window.prompt('What is wrong with this content?');

    if (!reason?.trim()) {
        return;
    }

    const url =
        type === 'review'
            ? `/reviews/${item.id}/report`
            : `/blog/${item.slug}/report`;
    router.post(url, { reason: reason.trim() }, { preserveScroll: true });
};
</script>

<template>
    <div class="min-h-screen bg-zinc-950 text-white">
        <section
            class="relative overflow-hidden border-b border-zinc-800 bg-zinc-900"
            :style="{
                backgroundImage: profileUser.banner_url
                    ? `linear-gradient(to right, rgba(9,9,11,.98), rgba(9,9,11,.7), rgba(9,9,11,.95)), url(${profileUser.banner_url})`
                    : null,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
            }"
        >
            <div
                class="absolute inset-0 bg-gradient-to-b from-transparent to-zinc-950/80"
            />

            <div
                class="relative mx-auto flex max-w-7xl flex-col items-center gap-4 px-4 py-10 text-center sm:flex-row sm:items-end sm:gap-8 sm:px-8 sm:py-20 sm:text-left"
            >
                <img
                    v-if="profileUser.avatar"
                    :src="profileUser.avatar"
                    :alt="profileUser.name"
                    class="h-24 w-24 rounded-2xl border-4 border-zinc-950 object-cover shadow-2xl sm:h-32 sm:w-32 sm:rounded-3xl"
                />

                <div>
                    <p
                        class="text-sm font-bold tracking-[0.3em] text-indigo-300 uppercase"
                    >
                        Public Profile
                    </p>

                    <h1
                        class="mt-2 text-3xl font-black tracking-tight break-words sm:mt-3 sm:text-6xl"
                    >
                        {{ profileUser.name }}
                    </h1>

                    <p
                        class="mt-2 text-sm break-all text-zinc-400 sm:mt-3 sm:text-base"
                    >
                        Steam ID: {{ profileUser.steam_id }}
                    </p>
                </div>
            </div>
        </section>

        <main
            class="mx-auto max-w-7xl space-y-6 px-3 py-6 sm:space-y-10 sm:px-8 sm:py-10"
        >
            <section
                class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-4 shadow-2xl shadow-black/20 sm:rounded-3xl sm:p-6"
            >
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black sm:text-3xl">
                            Featured Games
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            Games selected by this user for their public
                            showcase.
                        </p>
                    </div>

                    <span
                        class="rounded-full border border-zinc-700 bg-zinc-950 px-3 py-1 text-xs font-bold text-zinc-400"
                    >
                        {{ featuredGames.length }} featured
                    </span>
                </div>

                <div
                    v-if="featuredGames.length"
                    class="grid grid-cols-2 gap-3 sm:gap-5 md:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="item in featuredGames"
                        :key="item.id"
                        class="group min-w-0 overflow-hidden rounded-xl border border-zinc-800 bg-zinc-950 transition hover:-translate-y-1 hover:border-zinc-700 sm:rounded-3xl"
                    >
                        <div class="relative overflow-hidden">
                            <img
                                v-if="item.cover_url"
                                :src="item.cover_url"
                                :alt="item.title"
                                class="h-44 w-full object-cover transition duration-300 group-hover:scale-105"
                            />

                            <div
                                v-else
                                class="flex h-44 items-center justify-center bg-zinc-900 text-zinc-600"
                            >
                                <Package class="h-10 w-10" />
                            </div>

                            <div
                                class="absolute inset-0 bg-gradient-to-t from-zinc-950/80 to-transparent"
                            />
                        </div>

                        <div class="p-3 sm:p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-xl font-black">
                                        {{ item.title }}
                                    </h3>

                                    <p class="mt-1 text-sm text-zinc-500">
                                        {{ item.status }}
                                    </p>
                                </div>

                                <div
                                    v-if="item.rating"
                                    class="flex items-center gap-1 rounded-xl bg-white px-3 py-2 text-sm font-black text-zinc-950"
                                >
                                    <Star class="h-4 w-4" />
                                    {{ item.rating }}/10
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span
                                    v-if="item.recommended"
                                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-300"
                                >
                                    <ThumbsUp class="h-4 w-4" />
                                    Recommended
                                </span>

                                <span
                                    v-if="item.not_recommended"
                                    class="inline-flex items-center gap-2 rounded-xl bg-red-500/10 px-3 py-2 text-xs font-semibold text-red-300"
                                >
                                    <X class="h-4 w-4" />
                                    Not Recommended
                                </span>
                            </div>

                            <p class="mt-5 text-xs text-zinc-600">
                                Last updated {{ item.updated_at ?? 'recently' }}
                            </p>
                        </div>
                    </article>
                </div>

                <div
                    v-else
                    class="rounded-3xl border border-dashed border-zinc-800 bg-zinc-950 p-10 text-center"
                >
                    <h3 class="text-xl font-bold">No Featured Games Yet</h3>

                    <p class="mt-2 text-zinc-500">
                        This user has not featured any games yet.
                    </p>
                </div>
            </section>

            <section
                class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-4 shadow-2xl shadow-black/20 sm:rounded-3xl sm:p-6"
            >
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black sm:text-3xl">
                            Featured Reviews
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            Public reviews highlighted by this user.
                        </p>
                    </div>

                    <span
                        class="rounded-full border border-zinc-700 bg-zinc-950 px-3 py-1 text-xs font-bold text-zinc-400"
                    >
                        {{ featuredReviews.length }} featured
                    </span>
                </div>

                <div
                    v-if="featuredReviews.length"
                    class="grid gap-5 md:grid-cols-2"
                >
                    <article
                        v-for="review in featuredReviews"
                        :key="review.id"
                        class="rounded-xl border border-zinc-800 bg-zinc-950 p-4 transition hover:-translate-y-1 hover:border-zinc-700 sm:rounded-3xl sm:p-6"
                    >
                        <p class="text-sm font-bold text-indigo-300">
                            {{ review.game_title || 'Unknown game' }}
                        </p>

                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <h3 class="text-2xl font-black">
                                {{ review.title || 'Untitled review' }}
                            </h3>

                            <span
                                v-if="review.rating"
                                class="inline-flex items-center gap-1 rounded-xl bg-white px-3 py-1 text-sm font-black text-zinc-950"
                            >
                                <Star class="h-4 w-4" />
                                {{ review.rating }}/10
                            </span>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span
                                v-if="review.recommended"
                                class="inline-flex items-center gap-2 rounded-xl bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-300"
                            >
                                <ThumbsUp class="h-4 w-4" />
                                Recommended
                            </span>

                            <span
                                v-if="review.not_recommended"
                                class="inline-flex items-center gap-2 rounded-xl bg-red-500/10 px-3 py-2 text-xs font-semibold text-red-300"
                            >
                                <X class="h-4 w-4" />
                                Not Recommended
                            </span>
                        </div>

                        <p
                            class="mt-5 line-clamp-6 text-sm leading-6 whitespace-pre-line text-zinc-300"
                        >
                            {{ review.body }}
                        </p>

                        <p class="mt-5 text-xs text-zinc-600">
                            Posted {{ review.created_at ?? 'recently' }}
                        </p>
                    </article>
                </div>

                <div
                    v-else
                    class="rounded-3xl border border-dashed border-zinc-800 bg-zinc-950 p-10 text-center"
                >
                    <h3 class="text-xl font-bold">No Featured Reviews Yet</h3>

                    <p class="mt-2 text-zinc-500">
                        This user has not featured any reviews yet.
                    </p>
                </div>
            </section>

            <section
                class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-4 shadow-2xl shadow-black/20 sm:rounded-3xl sm:p-6"
            >
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black sm:text-3xl">
                            All public reviews
                        </h2>
                        <p class="mt-1 text-sm text-zinc-500">
                            Every public game review published by this user.
                        </p>
                    </div>
                    <span
                        class="rounded-full border border-zinc-700 bg-zinc-950 px-3 py-1 text-xs font-bold text-zinc-400"
                        >{{ publicReviews.length }} reviews</span
                    >
                </div>

                <div v-if="publicReviews.length" class="space-y-4">
                    <article
                        v-for="review in publicReviews"
                        :key="review.id"
                        class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5 sm:p-6"
                    >
                        <div
                            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-indigo-300">
                                    {{ review.game_title }}
                                </p>
                                <h3 class="mt-1 text-2xl font-black">
                                    {{ review.title || 'Untitled review' }}
                                </h3>
                            </div>
                            <span
                                v-if="review.rating"
                                class="shrink-0 rounded-xl bg-white px-3 py-2 text-sm font-black text-zinc-950"
                                >{{ review.rating }}/10</span
                            >
                        </div>
                        <RichTextContent
                            :content="review.body"
                            class="mt-4 line-clamp-6 text-sm leading-7 text-zinc-300"
                        />
                        <div
                            class="mt-5 flex flex-wrap items-center gap-2 border-t border-zinc-800 pt-4"
                        >
                            <div
                                class="inline-flex items-center rounded-xl border border-zinc-700"
                            >
                                <button
                                    type="button"
                                    :disabled="isOwnProfile"
                                    class="p-2.5 hover:text-emerald-400 disabled:opacity-30"
                                    :class="
                                        review.user_vote === 1
                                            ? 'text-emerald-400'
                                            : 'text-zinc-400'
                                    "
                                    aria-label="Upvote review"
                                    @click="voteReview(review, 1)"
                                >
                                    <ArrowUp class="h-4 w-4" />
                                </button>
                                <strong class="min-w-9 text-center text-sm">{{
                                    review.score
                                }}</strong>
                                <button
                                    type="button"
                                    :disabled="isOwnProfile"
                                    class="p-2.5 hover:text-red-400 disabled:opacity-30"
                                    :class="
                                        review.user_vote === -1
                                            ? 'text-red-400'
                                            : 'text-zinc-400'
                                    "
                                    aria-label="Downvote review"
                                    @click="voteReview(review, -1)"
                                >
                                    <ArrowDown class="h-4 w-4" />
                                </button>
                            </div>
                            <a
                                :href="review.url"
                                class="rounded-xl border border-zinc-700 px-3 py-2 text-sm font-bold text-zinc-300 hover:text-white"
                                >Read review</a
                            >
                            <button
                                v-if="!isOwnProfile"
                                type="button"
                                class="ml-auto inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-zinc-500 hover:bg-zinc-900 hover:text-red-300"
                                @click="reportContent('review', review)"
                            >
                                <Flag class="h-4 w-4" /> Report
                            </button>
                        </div>
                    </article>
                </div>
                <p
                    v-else
                    class="rounded-2xl border border-dashed border-zinc-800 p-8 text-center text-zinc-500"
                >
                    No public reviews yet.
                </p>
            </section>

            <section
                class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-4 shadow-2xl shadow-black/20 sm:rounded-3xl sm:p-6"
            >
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black sm:text-3xl">
                            Blog posts
                        </h2>
                        <p class="mt-1 text-sm text-zinc-500">
                            All published articles from this user.
                        </p>
                    </div>
                    <span
                        class="rounded-full border border-zinc-700 bg-zinc-950 px-3 py-1 text-xs font-bold text-zinc-400"
                        >{{ publicBlogPosts.length }} posts</span
                    >
                </div>

                <div
                    v-if="publicBlogPosts.length"
                    class="grid gap-4 md:grid-cols-2"
                >
                    <article
                        v-for="post in publicBlogPosts"
                        :key="post.id"
                        class="flex flex-col rounded-2xl border border-zinc-800 bg-zinc-950 p-5 sm:p-6"
                    >
                        <BookOpen class="h-6 w-6 text-indigo-300" />
                        <h3 class="mt-4 text-2xl font-black">
                            {{ post.title }}
                        </h3>
                        <p
                            class="mt-3 line-clamp-5 text-sm leading-7 text-zinc-400"
                        >
                            {{ post.excerpt }}
                        </p>
                        <div
                            class="mt-auto flex flex-wrap items-center gap-2 border-t border-zinc-800 pt-5"
                        >
                            <div
                                class="inline-flex items-center rounded-xl border border-zinc-700"
                            >
                                <button
                                    type="button"
                                    :disabled="isOwnProfile"
                                    class="p-2.5 hover:text-emerald-400 disabled:opacity-30"
                                    :class="
                                        post.user_vote === 1
                                            ? 'text-emerald-400'
                                            : 'text-zinc-400'
                                    "
                                    aria-label="Upvote post"
                                    @click="votePost(post, 1)"
                                >
                                    <ArrowUp class="h-4 w-4" />
                                </button>
                                <strong class="min-w-9 text-center text-sm">{{
                                    post.score
                                }}</strong>
                                <button
                                    type="button"
                                    :disabled="isOwnProfile"
                                    class="p-2.5 hover:text-red-400 disabled:opacity-30"
                                    :class="
                                        post.user_vote === -1
                                            ? 'text-red-400'
                                            : 'text-zinc-400'
                                    "
                                    aria-label="Downvote post"
                                    @click="votePost(post, -1)"
                                >
                                    <ArrowDown class="h-4 w-4" />
                                </button>
                            </div>
                            <a
                                :href="post.url"
                                class="rounded-xl border border-zinc-700 px-3 py-2 text-sm font-bold text-zinc-300 hover:text-white"
                                >Read post</a
                            >
                            <button
                                v-if="!isOwnProfile"
                                type="button"
                                class="ml-auto rounded-xl p-2 text-zinc-500 hover:bg-zinc-900 hover:text-red-300"
                                aria-label="Report post"
                                @click="reportContent('post', post)"
                            >
                                <Flag class="h-4 w-4" />
                            </button>
                        </div>
                    </article>
                </div>
                <p
                    v-else
                    class="rounded-2xl border border-dashed border-zinc-800 p-8 text-center text-zinc-500"
                >
                    No published blog posts yet.
                </p>
            </section>

            <section
                class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-4 shadow-2xl shadow-black/20 sm:rounded-3xl sm:p-6"
            >
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black sm:text-3xl">
                            Public Custom Lists
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            Public lists created by this user.
                        </p>
                    </div>

                    <span
                        class="rounded-full border border-zinc-700 bg-zinc-950 px-3 py-1 text-xs font-bold text-zinc-400"
                    >
                        {{ publicCustomLists.length }} public
                    </span>
                </div>

                <div
                    v-if="publicCustomLists.length"
                    class="grid gap-5 md:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="list in publicCustomLists"
                        :key="list.id"
                        class="rounded-xl border border-zinc-800 bg-zinc-950 p-4 transition hover:-translate-y-1 hover:border-zinc-700 sm:rounded-3xl sm:p-6"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-300"
                            >
                                <List class="h-6 w-6" />
                            </div>

                            <div>
                                <p
                                    class="text-xs font-bold tracking-widest text-indigo-300 uppercase"
                                >
                                    Custom List
                                </p>

                                <h3 class="mt-1 text-2xl font-black">
                                    {{ list.title }}
                                </h3>
                            </div>
                        </div>

                        <p
                            v-if="list.description"
                            class="mt-4 line-clamp-4 text-sm leading-6 text-zinc-300"
                        >
                            {{ list.description }}
                        </p>

                        <p v-else class="mt-4 text-sm text-zinc-500">
                            No description.
                        </p>

                        <p class="mt-5 text-xs text-zinc-600">
                            {{ list.items_count }} items · Created
                            {{ list.created_at ?? 'recently' }}
                        </p>

                        <button
                            type="button"
                            class="mt-4 rounded-xl bg-white px-4 py-2 text-sm font-bold text-black transition hover:bg-zinc-200"
                            @click="openList(list)"
                        >
                            View list
                        </button>
                    </article>
                </div>

                <div
                    v-else
                    class="rounded-3xl border border-dashed border-zinc-800 bg-zinc-950 p-10 text-center"
                >
                    <h3 class="text-xl font-bold">
                        No Public Custom Lists Yet
                    </h3>

                    <p class="mt-2 text-zinc-500">
                        This user has not made any custom lists public yet.
                    </p>
                </div>
            </section>

            <section
                class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-4 shadow-2xl shadow-black/20 sm:rounded-3xl sm:p-6"
            >
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black sm:text-3xl">
                            Featured Wardrobe Items
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            Cosmetics and profile items selected by this user.
                        </p>
                    </div>

                    <span
                        class="rounded-full border border-zinc-700 bg-zinc-950 px-3 py-1 text-xs font-bold text-zinc-400"
                    >
                        {{ featuredWardrobeItems.length }} featured
                    </span>
                </div>

                <div
                    v-if="featuredWardrobeItems.length"
                    class="grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4"
                >
                    <article
                        v-for="item in featuredWardrobeItems"
                        :key="item.id"
                        class="group min-w-0 overflow-hidden rounded-xl border border-zinc-800 bg-zinc-950 transition hover:-translate-y-1 hover:border-zinc-700 sm:rounded-3xl"
                    >
                        <div
                            class="flex h-44 items-center justify-center overflow-hidden bg-zinc-900"
                        >
                            <img
                                v-if="item.image_url"
                                :src="item.image_url"
                                :alt="item.name"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            />

                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center text-sm text-zinc-500"
                            >
                                <Package class="h-8 w-8" />
                            </div>
                        </div>

                        <div class="p-5">
                            <p
                                class="text-xs font-bold tracking-widest text-indigo-300 uppercase"
                            >
                                {{ item.type }}
                            </p>

                            <h3 class="mt-1 text-lg font-black">
                                {{ item.name }}
                            </h3>

                            <p class="mt-2 line-clamp-3 text-sm text-zinc-400">
                                {{ item.description }}
                            </p>
                        </div>
                    </article>
                </div>

                <div
                    v-else
                    class="rounded-3xl border border-dashed border-zinc-800 bg-zinc-950 p-10 text-center"
                >
                    <h3 class="text-xl font-bold">
                        No Featured Wardrobe Items Yet
                    </h3>

                    <p class="mt-2 text-zinc-500">
                        This user has not featured any wardrobe items yet.
                    </p>
                </div>
            </section>
        </main>

        <div
            v-if="selectedList"
            class="fixed inset-0 z-50 flex items-end justify-center bg-black/80 sm:items-center sm:p-6"
            @click.self="closeList"
        >
            <div
                class="max-h-[94dvh] w-full max-w-4xl overflow-y-auto rounded-t-3xl border border-zinc-800 bg-zinc-950 shadow-2xl sm:max-h-[90vh] sm:rounded-3xl"
            >
                <div
                    class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-800 bg-zinc-950/95 p-5 backdrop-blur"
                >
                    <div>
                        <p
                            class="text-xs font-bold tracking-[0.2em] text-indigo-300 uppercase"
                        >
                            Custom List
                        </p>

                        <h2 class="mt-1 text-2xl font-black">
                            {{ selectedList.title }}
                        </h2>
                    </div>

                    <button
                        type="button"
                        class="rounded-xl bg-zinc-800 p-2 text-zinc-300 transition hover:bg-zinc-700 hover:text-white"
                        @click="closeList"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="p-6">
                    <p
                        v-if="selectedList.description"
                        class="mb-6 text-sm leading-7 whitespace-pre-line text-zinc-300"
                    >
                        {{ selectedList.description }}
                    </p>

                    <div
                        v-if="selectedList.items?.length"
                        class="grid gap-4 md:grid-cols-2"
                    >
                        <div
                            v-for="game in selectedList.items"
                            :key="game.id"
                            class="flex gap-4 rounded-2xl border border-zinc-800 bg-zinc-900 p-4"
                        >
                            <img
                                v-if="game.cover_url"
                                :src="game.cover_url"
                                :alt="game.title"
                                class="h-24 w-16 rounded-xl object-cover"
                            />

                            <div
                                v-else
                                class="flex h-24 w-16 shrink-0 items-center justify-center rounded-xl bg-zinc-800 text-zinc-500"
                            >
                                <Package class="h-7 w-7" />
                            </div>

                            <div>
                                <p class="text-xs font-bold text-zinc-500">
                                    #{{ game.position ?? '' }}
                                </p>

                                <h3 class="mt-1 font-black">
                                    {{ game.title }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <p
                        v-else
                        class="rounded-2xl border border-dashed border-zinc-800 p-8 text-center text-sm text-zinc-500"
                    >
                        This list has no games yet.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
