<script setup>
import { ref } from 'vue'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'
import CuratorManager from '@/components/mini-curators/CuratorManager.vue'
import FeedPost from '@/components/mini-curators/FeedPost.vue'
import ReviewModal from '@/components/mini-curators/ReviewModal.vue'
import ListModal from '@/components/mini-curators/ListModal.vue'

defineProps({
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

const showCuratorManager = ref(false)
const selectedReview = ref(null)
const selectedList = ref(null)
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

                <CuratorManager
                    v-if="showCuratorManager"
                    :curators="curators"
                />

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
                        <FeedPost
                            v-for="item in feed"
                            :key="item.id"
                            :item="item"
                            @open-review="selectedReview = $event"
                            @open-list="selectedList = $event"
                        />
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

        <ReviewModal
            v-if="selectedReview"
            :review="selectedReview"
            @close="selectedReview = null"
        />

        <ListModal
            v-if="selectedList"
            :list="selectedList"
            @close="selectedList = null"
        />
    </div>
</template>