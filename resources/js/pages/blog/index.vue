<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ArrowRight, PenLine, Search, TrendingUp, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import BlogHeader from '@/components/blog/BlogHeader.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';

const props = defineProps({
    posts: {
        type: Object,
        default: () => ({ data: [] }),
    },
    categories: { type: Object, default: () => ({}) },
    activeCategory: { type: String, default: null },
    search: { type: String, default: '' },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const searchQuery = ref(props.search);

const loadPosts = (category: string | null = props.activeCategory) => {
    const parameters: Record<string, string> = {};

    if (category) {
        parameters.category = category;
    }

    if (searchQuery.value.trim()) {
        parameters.q = searchQuery.value.trim();
    }

    router.get('/blog', parameters, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const filterCategory = (category: string | null) => {
    loadPosts(category);
};

const clearSearch = () => {
    searchQuery.value = '';
    loadPosts();
};
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
                <a
                    :href="
                        user
                            ? '/blog/create'
                            : '/auth/steam?intended=/blog/create'
                    "
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-black text-zinc-950"
                >
                    <PenLine class="h-4 w-4" /> Write a post
                </a>
            </header>

            <form
                class="relative mt-8 max-w-2xl"
                role="search"
                @submit.prevent="loadPosts()"
            >
                <Search
                    class="pointer-events-none absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-zinc-500"
                />
                <input
                    v-model="searchQuery"
                    type="search"
                    maxlength="100"
                    placeholder="Search articles..."
                    class="w-full rounded-2xl border border-zinc-700 bg-zinc-900 py-3.5 pr-24 pl-12 text-white outline-none placeholder:text-zinc-500 focus:border-indigo-400"
                />
                <button
                    v-if="searchQuery"
                    type="button"
                    class="absolute top-1/2 right-16 -translate-y-1/2 p-2 text-zinc-500 hover:text-white"
                    aria-label="Clear search"
                    @click="clearSearch"
                >
                    <X class="h-4 w-4" />
                </button>
                <button
                    type="submit"
                    class="absolute top-1/2 right-2 -translate-y-1/2 rounded-xl bg-white px-3 py-2 text-xs font-black text-zinc-950"
                >
                    Search
                </button>
            </form>

            <nav class="mt-8 flex flex-wrap gap-2" aria-label="Blog categories">
                <button
                    type="button"
                    class="rounded-full border px-4 py-2 text-sm font-bold"
                    :class="
                        !activeCategory
                            ? 'border-white bg-white text-zinc-950'
                            : 'border-zinc-700 text-zinc-300'
                    "
                    @click="filterCategory(null)"
                >
                    All
                </button>
                <button
                    v-for="(label, value) in categories"
                    :key="value"
                    type="button"
                    class="rounded-full border px-4 py-2 text-sm font-bold"
                    :class="
                        activeCategory === value
                            ? 'border-white bg-white text-zinc-950'
                            : 'border-zinc-700 text-zinc-300'
                    "
                    @click="filterCategory(value)"
                >
                    {{ label }}
                </button>
            </nav>

            <section
                v-if="posts.data.length"
                class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3"
            >
                <Link
                    v-for="post in posts.data"
                    :key="post.id"
                    :href="post.url"
                    class="group flex min-h-64 min-w-0 flex-col rounded-2xl border border-zinc-800 bg-zinc-900 p-6 [overflow-wrap:anywhere] transition hover:-translate-y-1 hover:border-zinc-700"
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
                    <span
                        class="mt-3 w-fit rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-bold text-indigo-300"
                        >{{ post.category_label }}</span
                    >
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
                <h2 class="text-2xl font-black">No posts found</h2>
                <p class="mt-2 text-zinc-500">
                    Try another search phrase or category.
                </p>
            </section>

            <PaginationLinks v-if="posts.data.length" :pagination="posts" />
        </main>
    </div>
</template>
