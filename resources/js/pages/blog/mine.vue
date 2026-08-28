<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, EyeOff, PenLine, Trash2 } from 'lucide-vue-next';

import Sidebar from '@/components/layout/Sidebar.vue';
import Topbar from '@/components/layout/Topbar.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';

defineProps({
    user: Object,
    posts: {
        type: Object,
        default: () => ({ data: [] }),
    },
});

const removePost = (post: { title: string; slug: string }) => {
    if (window.confirm(`Delete "${post.title}"?`)) {
        router.delete(`/blog/${post.slug}`);
    }
};
</script>

<template>
    <Head title="My Blog Posts" />
    <div class="flex min-h-screen bg-zinc-950 text-white">
        <Sidebar />
        <div class="flex min-w-0 flex-1 flex-col">
            <Topbar :user="user" />
            <main class="flex-1 px-4 py-6 sm:px-8 sm:py-10">
                <div class="mx-auto max-w-6xl">
                    <header
                        class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-black tracking-[0.2em] text-indigo-400 uppercase"
                            >
                                Community blog
                            </p>
                            <h1 class="mt-2 text-3xl font-black sm:text-5xl">
                                My posts
                            </h1>
                            <p class="mt-3 text-zinc-400">
                                Manage published articles and private drafts.
                            </p>
                        </div>
                        <Link
                            href="/blog/create"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-black text-zinc-950"
                        >
                            <PenLine class="h-4 w-4" /> New post
                        </Link>
                    </header>

                    <section v-if="posts.data.length" class="mt-8 space-y-3">
                        <article
                            v-for="post in posts.data"
                            :key="post.id"
                            class="min-w-0 rounded-2xl border border-zinc-800 bg-zinc-900 p-5 [overflow-wrap:anywhere]"
                        >
                            <div
                                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h2 class="truncate text-xl font-black">
                                            {{ post.title }}
                                        </h2>
                                        <span
                                            class="inline-flex shrink-0 items-center gap-1 rounded-full border border-zinc-700 px-2 py-1 text-xs text-zinc-400"
                                        >
                                            <Eye
                                                v-if="post.is_published"
                                                class="h-3 w-3"
                                            />
                                            <EyeOff v-else class="h-3 w-3" />
                                            {{
                                                post.is_published
                                                    ? 'Published'
                                                    : 'Draft'
                                            }}
                                        </span>
                                    </div>
                                    <p
                                        class="mt-2 line-clamp-2 text-sm text-zinc-500"
                                    >
                                        {{ post.excerpt }}
                                    </p>
                                </div>
                                <div class="flex shrink-0 gap-2">
                                    <Link
                                        :href="`/blog/${post.slug}/edit`"
                                        class="rounded-xl bg-white px-4 py-2 text-sm font-black text-zinc-950"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        class="rounded-xl border border-zinc-700 p-2 text-zinc-400 hover:text-red-400"
                                        aria-label="Delete post"
                                        @click="removePost(post)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </article>
                    </section>
                    <div
                        v-else
                        class="mt-8 rounded-3xl border border-dashed border-zinc-700 py-20 text-center"
                    >
                        <h2 class="text-2xl font-black">No posts yet</h2>
                        <p class="mt-2 text-zinc-500">
                            Start with a draft or publish your first article.
                        </p>
                    </div>
                    <PaginationLinks
                        v-if="posts.data.length"
                        :pagination="posts"
                    />
                </div>
            </main>
        </div>
    </div>
</template>
