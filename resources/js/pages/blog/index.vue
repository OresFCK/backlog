<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, PenLine, TrendingUp } from 'lucide-vue-next';

import BlogHeader from '@/components/blog/BlogHeader.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';

defineProps({
    posts: {
        type: Object,
        default: () => ({ data: [] }),
    },
});
</script>

<template>
    <Head title="Community Blog | Curator.gg" />
    <div class="min-h-screen bg-zinc-950 text-white">
        <BlogHeader />
        <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-16">
            <header
                class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-black tracking-[0.2em] text-indigo-400 uppercase"
                    >
                        From players
                    </p>
                    <h1 class="mt-3 text-4xl font-black sm:text-6xl">
                        Community blog
                    </h1>
                    <p class="mt-4 max-w-2xl text-zinc-400">
                        Guides, opinions, discoveries and stories written by the
                        Curator.gg community.
                    </p>
                </div>
                <Link
                    href="/blog/create"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-black text-zinc-950"
                >
                    <PenLine class="h-4 w-4" /> Write a post
                </Link>
            </header>

            <section
                v-if="posts.data.length"
                class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3"
            >
                <Link
                    v-for="post in posts.data"
                    :key="post.id"
                    :href="post.url"
                    class="group flex min-h-64 flex-col rounded-2xl border border-zinc-800 bg-zinc-900 p-6 transition hover:-translate-y-1 hover:border-zinc-700"
                >
                    <img
                        v-if="post.cover_url"
                        :src="post.cover_url"
                        :alt="post.title"
                        class="-mx-6 -mt-6 mb-5 h-44 w-[calc(100%+3rem)] rounded-t-2xl object-cover"
                    />
                    <div class="flex items-center gap-3">
                        <img
                            v-if="post.author.avatar"
                            :src="post.author.avatar"
                            :alt="post.author.name"
                            class="h-9 w-9 rounded-xl object-cover"
                        />
                        <div>
                            <p class="text-sm font-bold">
                                {{ post.author.name || 'Curator.gg player' }}
                            </p>
                            <p class="text-xs text-zinc-500">
                                {{ post.published_at }}
                            </p>
                        </div>
                    </div>
                    <h2
                        class="mt-6 text-2xl font-black group-hover:text-indigo-300"
                    >
                        {{ post.title }}
                    </h2>
                    <p
                        class="mt-3 line-clamp-3 text-sm leading-6 text-zinc-400"
                    >
                        {{ post.excerpt }}
                    </p>
                    <div
                        class="mt-auto flex items-center justify-between pt-6 text-sm font-bold"
                    >
                        <span
                            class="inline-flex items-center gap-1 text-zinc-400"
                        >
                            <TrendingUp class="h-4 w-4" />
                            {{ post.score }}
                        </span>
                        <span class="inline-flex items-center gap-1">
                            Read <ArrowRight class="h-4 w-4" />
                        </span>
                    </div>
                </Link>
            </section>

            <section
                v-else
                class="mt-10 rounded-3xl border border-dashed border-zinc-700 py-20 text-center"
            >
                <h2 class="text-2xl font-black">No posts yet</h2>
                <p class="mt-2 text-zinc-500">
                    Be the first person to publish something.
                </p>
            </section>

            <PaginationLinks v-if="posts.data.length" :pagination="posts" />
        </main>
    </div>
</template>
