<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
    friendsRanking: {
        type: Array,
        default: () => [],
    },

    globalRanking: {
        type: Array,
        default: () => [],
    },
})

const showAllFriends = ref(false)
const showAllGlobal = ref(false)

const visibleFriendsRanking = computed(() =>
    showAllFriends.value
        ? props.friendsRanking
        : props.friendsRanking.slice(0, 1)
)

const visibleGlobalRanking = computed(() =>
    showAllGlobal.value
        ? props.globalRanking
        : props.globalRanking.slice(0, 1)
)
</script>

<template>
    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-indigo-400">
                        Friends ranking
                    </p>

                    <h2 class="mt-2 text-3xl font-black text-white">
                        Recommended by your people
                    </h2>
                </div>

                <button
                    v-if="friendsRanking.length > 1"
                    type="button"
                    class="rounded-xl border border-zinc-700 px-3 py-2 text-xs font-bold text-zinc-300 hover:bg-zinc-800"
                    @click="showAllFriends = !showAllFriends"
                >
                    {{ showAllFriends ? 'Show less' : `Show all (${friendsRanking.length})` }}
                </button>
            </div>

            <div class="space-y-4">
                <article
                    v-for="(item, index) in visibleFriendsRanking"
                    :key="item.game.id"
                    class="flex gap-4 rounded-2xl border border-zinc-800 bg-zinc-950 p-4"
                >
                    <div class="flex w-12 items-center justify-center text-2xl font-black text-zinc-500">
                        #{{ index + 1 }}
                    </div>

                    <img
                        :src="item.game.header_image_url"
                        class="h-24 w-40 rounded-xl object-cover"
                    />

                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-bold text-white">
                            {{ item.game.title }}
                        </h3>

                        <p class="mt-2 text-sm text-zinc-400">
                            {{ item.reason }}
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300">
                                Score {{ item.score }}
                            </span>
                        </div>
                    </div>
                </article>

                <div
                    v-if="!friendsRanking.length"
                    class="rounded-2xl border border-dashed border-zinc-800 p-8 text-center text-zinc-500"
                >
                    No friend recommendations yet.
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-purple-400">
                        Global ranking
                    </p>

                    <h2 class="mt-2 text-3xl font-black text-white">
                        Community favorites
                    </h2>
                </div>

                <button
                    v-if="globalRanking.length > 1"
                    type="button"
                    class="rounded-xl border border-zinc-700 px-3 py-2 text-xs font-bold text-zinc-300 hover:bg-zinc-800"
                    @click="showAllGlobal = !showAllGlobal"
                >
                    {{ showAllGlobal ? 'Show less' : `Show all (${globalRanking.length})` }}
                </button>
            </div>

            <div class="space-y-4">
                <article
                    v-for="(item, index) in visibleGlobalRanking"
                    :key="item.game.id"
                    class="flex gap-4 rounded-2xl border border-zinc-800 bg-zinc-950 p-4"
                >
                    <div class="flex w-12 items-center justify-center text-2xl font-black text-zinc-500">
                        #{{ index + 1 }}
                    </div>

                    <img
                        :src="item.game.header_image_url"
                        class="h-24 w-40 rounded-xl object-cover"
                    />

                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-bold text-white">
                            {{ item.game.title }}
                        </h3>

                        <p class="mt-2 text-sm text-zinc-400">
                            {{ item.reason }}
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300">
                                Score {{ item.score }}
                            </span>
                        </div>
                    </div>
                </article>

                <div
                    v-if="!globalRanking.length"
                    class="rounded-2xl border border-dashed border-zinc-800 p-8 text-center text-zinc-500"
                >
                    No global recommendations yet.
                </div>
            </div>
        </section>
    </div>
</template>