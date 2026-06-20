<script setup>
import {
    List,
    Package,
    Star,
    ThumbsUp,
    X,
} from 'lucide-vue-next'

defineProps({
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
})
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
            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-zinc-950/80" />

            <div class="relative mx-auto flex max-w-7xl items-end gap-8 px-8 py-20">
                <img
                    v-if="profileUser.avatar"
                    :src="profileUser.avatar"
                    :alt="profileUser.name"
                    class="h-32 w-32 rounded-3xl border-4 border-zinc-950 object-cover shadow-2xl"
                />

                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.3em] text-indigo-300">
                        Public Profile
                    </p>

                    <h1 class="mt-3 text-6xl font-black tracking-tight">
                        {{ profileUser.name }}
                    </h1>

                    <p class="mt-3 text-zinc-400">
                        Steam ID: {{ profileUser.steam_id }}
                    </p>
                </div>
            </div>
        </section>

        <main class="mx-auto max-w-7xl space-y-10 px-8 py-10">
            <section class="rounded-3xl border border-zinc-800 bg-zinc-900/50 p-6 shadow-2xl shadow-black/20">
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-3xl font-black">
                            Featured Games
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            Games selected by this user for their public showcase.
                        </p>
                    </div>

                    <span class="rounded-full border border-zinc-700 bg-zinc-950 px-3 py-1 text-xs font-bold text-zinc-400">
                        {{ featuredGames.length }} featured
                    </span>
                </div>

                <div
                    v-if="featuredGames.length"
                    class="grid gap-5 md:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="item in featuredGames"
                        :key="item.id"
                        class="group overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-950 transition hover:-translate-y-1 hover:border-zinc-700"
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

                            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/80 to-transparent" />
                        </div>

                        <div class="p-6">
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
                    <h3 class="text-xl font-bold">
                        No Featured Games Yet
                    </h3>

                    <p class="mt-2 text-zinc-500">
                        This user has not featured any games yet.
                    </p>
                </div>
            </section>

            <section class="rounded-3xl border border-zinc-800 bg-zinc-900/50 p-6 shadow-2xl shadow-black/20">
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-3xl font-black">
                            Featured Reviews
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            Public reviews highlighted by this user.
                        </p>
                    </div>

                    <span class="rounded-full border border-zinc-700 bg-zinc-950 px-3 py-1 text-xs font-bold text-zinc-400">
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
                        class="rounded-3xl border border-zinc-800 bg-zinc-950 p-6 transition hover:-translate-y-1 hover:border-zinc-700"
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

                        <p class="mt-5 line-clamp-6 whitespace-pre-line text-sm leading-6 text-zinc-300">
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
                    <h3 class="text-xl font-bold">
                        No Featured Reviews Yet
                    </h3>

                    <p class="mt-2 text-zinc-500">
                        This user has not featured any reviews yet.
                    </p>
                </div>
            </section>

            <section class="rounded-3xl border border-zinc-800 bg-zinc-900/50 p-6 shadow-2xl shadow-black/20">
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-3xl font-black">
                            Public Custom Lists
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            Public lists created by this user.
                        </p>
                    </div>

                    <span class="rounded-full border border-zinc-700 bg-zinc-950 px-3 py-1 text-xs font-bold text-zinc-400">
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
                        class="rounded-3xl border border-zinc-800 bg-zinc-950 p-6 transition hover:-translate-y-1 hover:border-zinc-700"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-300">
                                <List class="h-6 w-6" />
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-indigo-300">
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

                        <p
                            v-else
                            class="mt-4 text-sm text-zinc-500"
                        >
                            No description.
                        </p>

                        <p class="mt-5 text-xs text-zinc-600">
                            {{ list.items_count }} items · Created {{ list.created_at ?? 'recently' }}
                        </p>
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

            <section class="rounded-3xl border border-zinc-800 bg-zinc-900/50 p-6 shadow-2xl shadow-black/20">
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-3xl font-black">
                            Featured Wardrobe Items
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            Cosmetics and profile items selected by this user.
                        </p>
                    </div>

                    <span class="rounded-full border border-zinc-700 bg-zinc-950 px-3 py-1 text-xs font-bold text-zinc-400">
                        {{ featuredWardrobeItems.length }} featured
                    </span>
                </div>

                <div
                    v-if="featuredWardrobeItems.length"
                    class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <article
                        v-for="item in featuredWardrobeItems"
                        :key="item.id"
                        class="group overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-950 transition hover:-translate-y-1 hover:border-zinc-700"
                    >
                        <div class="flex h-44 items-center justify-center overflow-hidden bg-zinc-900">
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
                            <p class="text-xs font-bold uppercase tracking-widest text-indigo-300">
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
    </div>
</template>