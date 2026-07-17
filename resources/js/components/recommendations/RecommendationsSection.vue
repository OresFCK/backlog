<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    friendsRanking: {
        type: Array,
        default: () => [],
    },

    globalRanking: {
        type: Array,
        default: () => [],
    },
});

const showAllFriends = ref(false);
const showAllGlobal = ref(false);

const visibleFriendsRanking = computed(() =>
    showAllFriends.value
        ? props.friendsRanking
        : props.friendsRanking.slice(0, 1),
);

const visibleGlobalRanking = computed(() =>
    showAllGlobal.value ? props.globalRanking : props.globalRanking.slice(0, 1),
);
</script>

<template>
    <div class="grid gap-4 sm:gap-6 xl:grid-cols-2">
        <section
            class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4 sm:rounded-3xl sm:p-6"
        >
            <div
                class="mb-4 flex items-start justify-between gap-3 sm:mb-6 sm:gap-4"
            >
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-indigo-400 sm:text-sm">
                        Friends ranking
                    </p>

                    <h2
                        class="mt-1 text-xl leading-tight font-black text-white sm:mt-2 sm:text-3xl"
                    >
                        Recommended by your people
                    </h2>
                </div>

                <button
                    v-if="friendsRanking.length > 1"
                    type="button"
                    class="shrink-0 rounded-xl border border-zinc-700 px-3 py-2 text-center text-[11px] leading-tight font-bold text-zinc-300 hover:bg-zinc-800 sm:text-xs"
                    @click="showAllFriends = !showAllFriends"
                >
                    {{
                        showAllFriends
                            ? 'Show less'
                            : `Show all (${friendsRanking.length})`
                    }}
                </button>
            </div>

            <div class="space-y-4">
                <article
                    v-for="(item, index) in visibleFriendsRanking"
                    :key="item.game.id"
                    class="relative grid overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950 sm:grid-cols-[48px_160px_minmax(0,1fr)] sm:gap-4 sm:p-4"
                >
                    <div
                        class="absolute top-3 left-3 z-10 rounded-lg bg-black/80 px-2 py-1 text-sm font-black text-white backdrop-blur sm:static sm:flex sm:w-12 sm:items-center sm:justify-center sm:bg-transparent sm:p-0 sm:text-2xl sm:text-zinc-500"
                    >
                        #{{ index + 1 }}
                    </div>

                    <img
                        :src="item.game.header_image_url"
                        class="aspect-video h-auto w-full object-cover sm:h-24 sm:w-40 sm:rounded-xl"
                    />

                    <div class="min-w-0 p-4 sm:p-0">
                        <h3 class="text-base font-bold text-white sm:text-lg">
                            {{ item.game.title }}
                        </h3>

                        <p
                            class="mt-1.5 text-sm leading-6 text-zinc-400 sm:mt-2"
                        >
                            {{ item.reason }}
                        </p>

                        <div class="mt-3 flex flex-wrap gap-2 sm:mt-4">
                            <span
                                class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300"
                            >
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

        <section
            class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4 sm:rounded-3xl sm:p-6"
        >
            <div
                class="mb-4 flex items-start justify-between gap-3 sm:mb-6 sm:gap-4"
            >
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-purple-400 sm:text-sm">
                        Global ranking
                    </p>

                    <h2
                        class="mt-1 text-xl leading-tight font-black text-white sm:mt-2 sm:text-3xl"
                    >
                        Community favorites
                    </h2>
                </div>

                <button
                    v-if="globalRanking.length > 1"
                    type="button"
                    class="shrink-0 rounded-xl border border-zinc-700 px-3 py-2 text-center text-[11px] leading-tight font-bold text-zinc-300 hover:bg-zinc-800 sm:text-xs"
                    @click="showAllGlobal = !showAllGlobal"
                >
                    {{
                        showAllGlobal
                            ? 'Show less'
                            : `Show all (${globalRanking.length})`
                    }}
                </button>
            </div>

            <div class="space-y-4">
                <article
                    v-for="(item, index) in visibleGlobalRanking"
                    :key="item.game.id"
                    class="relative grid overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950 sm:grid-cols-[48px_160px_minmax(0,1fr)] sm:gap-4 sm:p-4"
                >
                    <div
                        class="absolute top-3 left-3 z-10 rounded-lg bg-black/80 px-2 py-1 text-sm font-black text-white backdrop-blur sm:static sm:flex sm:w-12 sm:items-center sm:justify-center sm:bg-transparent sm:p-0 sm:text-2xl sm:text-zinc-500"
                    >
                        #{{ index + 1 }}
                    </div>

                    <img
                        :src="item.game.header_image_url"
                        class="aspect-video h-auto w-full object-cover sm:h-24 sm:w-40 sm:rounded-xl"
                    />

                    <div class="min-w-0 p-4 sm:p-0">
                        <h3 class="text-base font-bold text-white sm:text-lg">
                            {{ item.game.title }}
                        </h3>

                        <p
                            class="mt-1.5 text-sm leading-6 text-zinc-400 sm:mt-2"
                        >
                            {{ item.reason }}
                        </p>

                        <div class="mt-3 flex flex-wrap gap-2 sm:mt-4">
                            <span
                                class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300"
                            >
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
