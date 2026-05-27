<script setup>
defineProps({
    friendsRanking: {
        type: Array,
        default: () => [],
    },

    globalRanking: {
        type: Array,
        default: () => [],
    },
})
</script>

<template>
    <div class="grid gap-6 xl:grid-cols-2">
        <section
            class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6"
        >
            <div class="mb-6">
                <p class="text-sm font-semibold text-indigo-400">
                    Friends ranking
                </p>

                <h2
                    class="mt-2 text-3xl font-black text-white"
                >
                    Recommended by your people
                </h2>

                <p class="mt-3 text-sm text-zinc-400">
                    Games most recommended by your friends
                    and followed users.
                </p>
            </div>

            <div class="space-y-4">
                <article
                    v-for="(item, index) in friendsRanking"
                    :key="item.game.id"
                    class="flex gap-4 rounded-2xl border border-zinc-800 bg-zinc-950 p-4"
                >
                    <div
                        class="flex w-12 items-center justify-center text-2xl font-black text-zinc-500"
                    >
                        #{{ index + 1 }}
                    </div>

                    <img
                        :src="item.game.header_image_url"
                        class="h-24 w-40 rounded-xl object-cover"
                    />

                    <div class="min-w-0 flex-1">
                        <div
                            class="flex flex-wrap items-center gap-2"
                        >
                            <h3
                                class="text-lg font-bold text-white"
                            >
                                {{ item.game.title }}
                            </h3>

                            <span
                                class="rounded-full bg-indigo-500/10 px-2 py-1 text-xs font-bold text-indigo-300"
                            >
                                Friends
                            </span>
                        </div>

                        <p
                            class="mt-2 line-clamp-2 text-sm text-zinc-400"
                        >
                            {{ item.reason }}
                        </p>

                        <div
                            class="mt-4 flex flex-wrap gap-2 text-xs font-bold"
                        >
                            <span
                                class="rounded-full bg-emerald-500/10 px-3 py-1 text-emerald-300"
                            >
                                {{
                                    item.recommendations_count
                                }}
                                recommends
                            </span>

                            <span
                                class="rounded-full bg-zinc-800 px-3 py-1 text-zinc-300"
                            >
                                Score
                                {{ item.score }}
                            </span>

                            <span
                                v-if="item.average_rating"
                                class="rounded-full bg-yellow-500/10 px-3 py-1 text-yellow-300"
                            >
                                ★
                                {{ item.average_rating }}/10
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
            class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6"
        >
            <div class="mb-6">
                <p class="text-sm font-semibold text-purple-400">
                    Global ranking
                </p>

                <h2
                    class="mt-2 text-3xl font-black text-white"
                >
                    Community favorites
                </h2>

                <p class="mt-3 text-sm text-zinc-400">
                    Most loved games across the entire
                    platform.
                </p>
            </div>

            <div class="space-y-4">
                <article
                    v-for="(item, index) in globalRanking"
                    :key="item.game.id"
                    class="flex gap-4 rounded-2xl border border-zinc-800 bg-zinc-950 p-4"
                >
                    <div
                        class="flex w-12 items-center justify-center text-2xl font-black text-zinc-500"
                    >
                        #{{ index + 1 }}
                    </div>

                    <img
                        :src="item.game.header_image_url"
                        class="h-24 w-40 rounded-xl object-cover"
                    />

                    <div class="min-w-0 flex-1">
                        <div
                            class="flex flex-wrap items-center gap-2"
                        >
                            <h3
                                class="text-lg font-bold text-white"
                            >
                                {{ item.game.title }}
                            </h3>

                            <span
                                class="rounded-full bg-purple-500/10 px-2 py-1 text-xs font-bold text-purple-300"
                            >
                                Global
                            </span>
                        </div>

                        <p
                            class="mt-2 line-clamp-2 text-sm text-zinc-400"
                        >
                            {{ item.reason }}
                        </p>

                        <div
                            class="mt-4 flex flex-wrap gap-2 text-xs font-bold"
                        >
                            <span
                                class="rounded-full bg-emerald-500/10 px-3 py-1 text-emerald-300"
                            >
                                {{
                                    item.recommendations_count
                                }}
                                recommends
                            </span>

                            <span
                                class="rounded-full bg-zinc-800 px-3 py-1 text-zinc-300"
                            >
                                Score
                                {{ item.score }}
                            </span>

                            <span
                                v-if="item.average_rating"
                                class="rounded-full bg-yellow-500/10 px-3 py-1 text-yellow-300"
                            >
                                ★
                                {{ item.average_rating }}/10
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