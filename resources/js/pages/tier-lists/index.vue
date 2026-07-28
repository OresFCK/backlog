<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Eye, Gamepad2, LogIn, Plus, Share2 } from 'lucide-vue-next';

import PaginationLinks from '@/components/PaginationLinks.vue';

const props = defineProps({
    auth: {
        type: Object,
        default: () => ({ user: null }),
    },
    user: Object,
    tierLists: {
        type: Object,
        default: () => ({ data: [] }),
    },
});

const isAuthenticated = () => Boolean(props.auth?.user || props.user);

const shareTierList = async (tierList) => {
    if (!tierList.is_public) {
        return;
    }

    if (navigator.share) {
        try {
            await navigator.share({
                title: tierList.title,
                text: `Check out ${tierList.title} on Curator.gg`,
                url: tierList.share_url,
            });

            return;
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
        }
    }

    await navigator.clipboard.writeText(tierList.share_url);
    window.alert('Public link copied.');
};
</script>

<template>
    <Head title="Community Tier Lists" />

    <div class="flex min-h-screen bg-zinc-950 text-white">
        <div class="flex min-w-0 flex-1 flex-col">
            <header
                class="sticky top-0 z-40 border-b border-zinc-800 bg-zinc-950/95 backdrop-blur"
            >
                <div
                    class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-4 sm:px-8"
                >
                    <Link
                        href="/home"
                        class="flex items-center gap-2 text-lg font-black"
                    >
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-zinc-950"
                        >
                            <Gamepad2 class="h-5 w-5" />
                        </span>
                        Curator.gg
                    </Link>
                    <nav
                        class="ml-auto flex items-center gap-2 text-sm font-bold"
                    >
                        <a
                            href="/tier-list-maker"
                            class="rounded-xl border border-zinc-700 px-3 py-2 text-zinc-200 hover:bg-zinc-900"
                        >
                            Create
                        </a>
                        <a
                            :href="
                                isAuthenticated()
                                    ? '/dashboard'
                                    : '/auth/steam?intended=/tier-lists'
                            "
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-zinc-950"
                        >
                            <LogIn class="h-4 w-4" />
                            <span class="hidden sm:inline">
                                {{
                                    isAuthenticated() ? 'Dashboard' : 'Sign in'
                                }}
                            </span>
                        </a>
                    </nav>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-8 sm:py-10">
                <div class="mx-auto max-w-7xl">
                    <header
                        class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-black tracking-[0.2em] text-indigo-400 uppercase"
                            >
                                Rankings
                            </p>
                            <h1 class="mt-2 text-3xl font-black sm:text-5xl">
                                Community tier lists
                            </h1>
                            <p class="mt-3 text-zinc-400">
                                Browse rankings made by players or create your
                                own without signing in.
                            </p>
                        </div>

                        <Link
                            href="/tier-list-maker"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-black text-zinc-950"
                        >
                            <Plus class="h-4 w-4" />
                            Create tier list
                        </Link>
                    </header>

                    <section
                        v-if="tierLists.data.length"
                        class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                    >
                        <article
                            v-for="tierList in tierLists.data"
                            :key="tierList.id"
                            class="flex min-w-0 flex-col rounded-2xl border border-zinc-800 bg-zinc-900 p-5 sm:rounded-3xl sm:p-6"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <h2
                                        class="truncate text-xl font-black text-white"
                                    >
                                        {{ tierList.title }}
                                    </h2>
                                    <p
                                        class="mt-2 line-clamp-2 min-h-10 text-sm leading-5 text-zinc-500"
                                    >
                                        {{
                                            tierList.description ||
                                            'No description yet.'
                                        }}
                                    </p>
                                    <p
                                        v-if="tierList.author?.name"
                                        class="mt-2 text-xs font-bold text-zinc-400"
                                    >
                                        by {{ tierList.author.name }}
                                    </p>
                                </div>

                                <span
                                    class="inline-flex shrink-0 items-center gap-1 rounded-full border border-zinc-700 px-2.5 py-1 text-xs font-bold text-zinc-400"
                                >
                                    <Eye class="h-3 w-3" />
                                    Public
                                </span>
                            </div>

                            <div class="mt-6 flex items-end justify-between">
                                <div>
                                    <p class="text-2xl font-black">
                                        {{ tierList.items_count }}
                                    </p>
                                    <p class="text-xs text-zinc-500">games</p>
                                </div>
                                <div
                                    class="flex max-w-36 flex-1 flex-col gap-1 pl-5"
                                >
                                    <div
                                        v-for="tier in tierList.tiers.slice(
                                            0,
                                            5,
                                        )"
                                        :key="tier.id"
                                        class="h-2 rounded-full"
                                        :style="{
                                            backgroundColor: tier.color,
                                        }"
                                    />
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-2 gap-2">
                                <Link
                                    :href="tierList.share_url"
                                    class="rounded-xl bg-white px-4 py-2.5 text-center text-sm font-black text-zinc-950"
                                >
                                    View
                                </Link>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-700 px-4 py-2.5 text-sm font-bold"
                                    @click="shareTierList(tierList)"
                                >
                                    <Share2 class="h-4 w-4" /> Share
                                </button>
                            </div>
                        </article>
                    </section>
                    <PaginationLinks
                        v-if="tierLists.data.length"
                        :pagination="tierLists"
                    />

                    <section
                        v-else
                        class="mt-8 rounded-3xl border border-dashed border-zinc-700 bg-zinc-900/40 px-6 py-20 text-center"
                    >
                        <h2 class="text-2xl font-black">No tier lists yet</h2>
                        <p class="mt-2 text-zinc-500">
                            Be the first to publish a game ranking.
                        </p>
                        <Link
                            href="/tier-list-maker"
                            class="mt-6 inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-black text-zinc-950"
                        >
                            <Plus class="h-4 w-4" /> Create tier list
                        </Link>
                    </section>
                </div>
            </main>
        </div>
    </div>
</template>
