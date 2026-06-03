<script setup>
import { Star, ThumbsUp, X } from 'lucide-vue-next'

defineProps({
    profileUser: Object,
    showcaseItems: Array,
})
</script>

<template>
    <div class="min-h-screen bg-zinc-950 text-white">
        <section
            class="relative border-b border-zinc-800 bg-zinc-900"
            :style="{
                backgroundImage: profileUser.banner_url
                    ? `linear-gradient(to right, rgba(9,9,11,.95), rgba(9,9,11,.65)), url(${profileUser.banner_url})`
                    : null,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
            }"
        >
            <div class="mx-auto flex max-w-6xl items-end gap-6 px-8 py-16">
                <img
                    v-if="profileUser.avatar"
                    :src="profileUser.avatar"
                    :alt="profileUser.name"
                    class="h-28 w-28 rounded-3xl border-4 border-zinc-950 object-cover"
                />

                <div>
                    <p
                        class="text-sm font-semibold uppercase tracking-widest text-zinc-400"
                    >
                        Public Profile
                    </p>

                    <h1 class="mt-2 text-5xl font-black">
                        {{ profileUser.name }}
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Steam ID: {{ profileUser.steam_id }}
                    </p>
                </div>
            </div>
        </section>

        <main class="mx-auto max-w-6xl px-8 py-10">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black">
                        Showcase
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        Featured games, reviews and gaming highlights.
                    </p>
                </div>
            </div>

            <div
                v-if="showcaseItems.length"
                class="grid gap-5 md:grid-cols-2 xl:grid-cols-3"
            >
                <article
                    v-for="item in showcaseItems"
                    :key="item.id"
                    class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold">
                                {{ item.title }}
                            </h3>

                            <p class="mt-1 text-sm text-zinc-500">
                                {{ item.status }}
                            </p>
                        </div>

                        <div
                            v-if="item.rating"
                            class="flex items-center gap-1 rounded-xl bg-zinc-950 px-3 py-2 text-sm font-bold"
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

                    <p
                        v-if="item.note"
                        class="mt-5 line-clamp-5 text-sm leading-6 text-zinc-300"
                    >
                        {{ item.note }}
                    </p>

                    <p class="mt-5 text-xs text-zinc-600">
                        Last updated {{ item.updated_at ?? 'recently' }}
                    </p>
                </article>
            </div>

            <div
                v-else
                class="rounded-3xl border border-dashed border-zinc-800 bg-zinc-900 p-12 text-center"
            >
                <h3 class="text-xl font-bold">
                    Nothing Featured Yet
                </h3>

                <p class="mt-2 text-zinc-500">
                    This user has not added anything to their showcase yet.
                </p>
            </div>
        </main>
    </div>
</template>