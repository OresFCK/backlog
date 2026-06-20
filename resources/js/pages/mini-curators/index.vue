<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Package, X } from 'lucide-vue-next'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

const props = defineProps({
    user: Object,
    curators: {
        type: Array,
        default: () => [],
    },
    feed: {
        type: Array,
        default: () => [],
    },
})

const searchQuery = ref('')
const showCuratorManager = ref(false)
const selectedReview = ref(null)
const selectedList = ref(null)

const filteredCurators = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()

    if (!query) {
        return props.curators
    }

    return props.curators.filter((curator) =>
        String(curator.name ?? '')
            .toLowerCase()
            .includes(query)
        || String(curator.steam_id ?? '')
            .toLowerCase()
            .includes(query)
    )
})

const follow = (curator) => {
    router.post(`/mini-curators/${curator.id}/follow`, {}, {
        preserveScroll: true,
    })
}

const unfollow = (curator) => {
    router.delete(`/mini-curators/${curator.id}/follow`, {
        preserveScroll: true,
    })
}

function openReview(item) {
    selectedReview.value = item
}

function closeReview() {
    selectedReview.value = null
}

function openList(item) {
    selectedList.value = item
}

function closeList() {
    selectedList.value = null
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950 text-white">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 space-y-6 p-8">
                <section class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-4xl font-black">
                            Mini Curators
                        </h1>

                        <p class="mt-2 max-w-2xl text-zinc-400">
                            Reviews, lists and activity from Mini Curators you follow.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-zinc-800"
                        @click="showCuratorManager = !showCuratorManager"
                    >
                        {{ showCuratorManager ? 'Hide Curators' : 'Find Curators' }}
                    </button>
                </section>

                <section
                    v-if="showCuratorManager"
                    class="rounded-3xl border border-zinc-800 bg-zinc-900 p-5"
                >
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold">
                                Follow Mini Curators
                            </h2>

                            <p class="mt-1 text-sm text-zinc-500">
                                {{ filteredCurators.length }} available
                            </p>
                        </div>

                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search Mini Curators..."
                            class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-500 md:w-80"
                        />
                    </div>

                    <div
                        v-if="filteredCurators.length"
                        class="mt-5 grid max-h-[360px] gap-3 overflow-y-auto pr-1 md:grid-cols-2 xl:grid-cols-3"
                    >
                        <div
                            v-for="curator in filteredCurators"
                            :key="curator.id"
                            class="flex items-center gap-3 rounded-2xl border border-zinc-800 bg-zinc-950 p-3"
                        >
                            <Link
                                :href="`/u/${curator.steam_id}`"
                                class="flex min-w-0 flex-1 items-center gap-3"
                            >
                                <img
                                    v-if="curator.avatar"
                                    :src="curator.avatar"
                                    :alt="curator.name"
                                    class="h-11 w-11 rounded-xl object-cover transition hover:opacity-80"
                                />

                                <div
                                    v-else
                                    class="h-11 w-11 rounded-xl bg-zinc-800 transition hover:bg-zinc-700"
                                />

                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-bold hover:text-indigo-300">
                                        {{ curator.name }}
                                    </p>

                                    <p class="truncate text-xs text-zinc-500">
                                        {{ curator.steam_id }}
                                    </p>
                                </div>
                            </Link>

                            <button
                                v-if="curator.is_following"
                                type="button"
                                class="rounded-xl border border-red-900/60 px-3 py-2 text-xs font-bold text-red-400 transition hover:bg-red-950/40"
                                @click="unfollow(curator)"
                            >
                                Unfollow
                            </button>

                            <button
                                v-else
                                type="button"
                                class="rounded-xl border border-purple-700 bg-purple-950/50 px-3 py-2 text-xs font-bold text-purple-200 transition hover:bg-purple-900/60"
                                @click="follow(curator)"
                            >
                                Follow
                            </button>
                        </div>
                    </div>

                    <p
                        v-else
                        class="mt-5 rounded-2xl border border-dashed border-zinc-700 p-6 text-center text-sm text-zinc-500"
                    >
                        No Mini Curators found.
                    </p>
                </section>

                <section class="min-h-[calc(100vh-230px)] rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold">
                            Wall
                        </h2>

                        <p class="text-sm text-zinc-500">
                            Only followed Mini Curators.
                        </p>
                    </div>

                    <div
                        v-if="feed.length"
                        class="mt-6 space-y-4"
                    >
                        <article
                            v-for="item in feed"
                            :key="item.id"
                            class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5"
                        >
                            <div class="flex items-center gap-3">
                                <Link
                                    v-if="item.user?.steam_id"
                                    :href="`/u/${item.user.steam_id}`"
                                >
                                    <img
                                        v-if="item.user.avatar"
                                        :src="item.user.avatar"
                                        :alt="item.user.name"
                                        class="h-10 w-10 rounded-xl object-cover transition hover:opacity-80"
                                    />

                                    <div
                                        v-else
                                        class="h-10 w-10 rounded-xl bg-zinc-800 transition hover:bg-zinc-700"
                                    />
                                </Link>

                                <div
                                    v-else
                                    class="h-10 w-10 rounded-xl bg-zinc-800"
                                />

                                <div>
                                    <Link
                                        v-if="item.user?.steam_id"
                                        :href="`/u/${item.user.steam_id}`"
                                        class="font-bold hover:text-indigo-300"
                                    >
                                        {{ item.user.name }}
                                    </Link>

                                    <p
                                        v-else
                                        class="font-bold"
                                    >
                                        {{ item.user.name }}
                                    </p>

                                    <p class="text-xs text-zinc-500">
                                        {{ item.created_at_human }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-purple-300">
                                    {{ item.type === 'review' ? 'Review' : 'Custom List' }}
                                </p>

                                <h3 class="mt-2 text-lg font-black">
                                    {{ item.title }}
                                </h3>

                                <p
                                    v-if="item.game_title"
                                    class="mt-1 text-sm text-zinc-500"
                                >
                                    {{ item.game_title }}
                                </p>

                                <p
                                    v-if="item.body"
                                    class="mt-3 line-clamp-4 text-sm text-zinc-300"
                                >
                                    {{ item.body }}
                                </p>

                                <p
                                    v-if="item.description"
                                    class="mt-3 line-clamp-4 text-sm text-zinc-300"
                                >
                                    {{ item.description }}
                                </p>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <button
                                        v-if="item.type === 'review'"
                                        type="button"
                                        class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-black transition hover:bg-zinc-200"
                                        @click="openReview(item)"
                                    >
                                        Read more
                                    </button>

                                    <button
                                        v-if="item.type !== 'review'"
                                        type="button"
                                        class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-black transition hover:bg-zinc-200"
                                        @click="openList(item)"
                                    >
                                        View list
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div
                        v-else
                        class="mt-6 flex min-h-[420px] items-center justify-center rounded-2xl border border-dashed border-zinc-700 bg-zinc-950 p-10 text-center"
                    >
                        <div>
                            <p class="text-lg font-bold">
                                Your wall is empty.
                            </p>

                            <p class="mt-2 text-sm text-zinc-500">
                                Follow Mini Curators to see their reviews, lists and activity here.
                            </p>
                        </div>
                    </div>
                </section>
            </main>
        </div>

        <div
            v-if="selectedReview"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-6"
            @click.self="closeReview"
        >
            <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-3xl border border-zinc-800 bg-zinc-950 shadow-2xl">
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-800 bg-zinc-950/95 p-5 backdrop-blur">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-purple-300">
                            Review
                        </p>

                        <h2 class="mt-1 text-2xl font-black">
                            {{ selectedReview.title }}
                        </h2>
                    </div>

                    <button
                        type="button"
                        class="rounded-xl bg-zinc-800 p-2 text-zinc-300 transition hover:bg-zinc-700 hover:text-white"
                        @click="closeReview"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-5 flex items-center gap-3">
                        <img
                            v-if="selectedReview.user?.avatar"
                            :src="selectedReview.user.avatar"
                            :alt="selectedReview.user.name"
                            class="h-11 w-11 rounded-xl object-cover"
                        />

                        <div
                            v-else
                            class="h-11 w-11 rounded-xl bg-zinc-800"
                        />

                        <div>
                            <p class="font-bold">
                                {{ selectedReview.user?.name }}
                            </p>

                            <p class="text-xs text-zinc-500">
                                {{ selectedReview.created_at_human }}
                            </p>
                        </div>
                    </div>

                    <p
                        v-if="selectedReview.game_title"
                        class="text-sm font-bold text-indigo-300"
                    >
                        {{ selectedReview.game_title }}
                    </p>

                    <img
                        v-if="selectedReview.screenshot_url"
                        :src="selectedReview.screenshot_url"
                        :alt="selectedReview.title"
                        class="mt-5 max-h-[520px] w-full rounded-2xl border border-zinc-800 object-cover"
                    />

                    <p
                        v-if="selectedReview.body"
                        class="mt-6 whitespace-pre-line text-sm leading-7 text-zinc-200"
                    >
                        {{ selectedReview.body }}
                    </p>
                </div>
            </div>
        </div>

        <div
            v-if="selectedList"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-6"
            @click.self="closeList"
        >
            <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-3xl border border-zinc-800 bg-zinc-950 shadow-2xl">
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-800 bg-zinc-950/95 p-5 backdrop-blur">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-300">
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
                        class="mb-6 whitespace-pre-line text-sm leading-7 text-zinc-300"
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