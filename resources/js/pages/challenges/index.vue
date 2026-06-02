<script setup>
import { router } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

defineProps({
    user: Object,
    challenges: {
        type: Array,
        default: () => [],
    },
})

const joinChallenge = (challenge) => {
    router.post(`/challenges/${challenge.id}/join`, {}, {
        preserveScroll: true,
    })
}

const completeChallenge = (challenge) => {
    router.post(`/challenges/${challenge.id}/complete`, {}, {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 space-y-8 p-8">
                <section class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-8">
                    <h1 class="text-3xl font-bold text-white">
                        Challenges
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Join challenges, complete them and earn XP, coins or shop items.
                    </p>
                </section>

                <section class="grid gap-6 lg:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="challenge in challenges"
                        :key="challenge.id"
                        class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6"
                    >
                        <h2 class="text-xl font-bold text-white">
                            {{ challenge.title }}
                        </h2>

                        <p class="mt-3 text-sm text-zinc-400">
                            {{ challenge.description }}
                        </p>

                        <div class="mt-5 space-y-2 text-sm">
                            <p
                                v-if="challenge.reward_xp"
                                class="text-zinc-300"
                            >
                                Reward: {{ challenge.reward_xp }} XP
                            </p>

                            <p
                                v-if="challenge.reward_coins"
                                class="text-zinc-300"
                            >
                                Coins: {{ challenge.reward_coins }}
                            </p>

                            <p
                                v-if="challenge.item"
                                class="text-zinc-300"
                            >
                                Item: {{ challenge.item.name }}
                            </p>
                        </div>

                        <div class="mt-6">
                            <button
                                v-if="!challenge.joined"
                                type="button"
                                class="w-full rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                                @click="joinChallenge(challenge)"
                            >
                                Join challenge
                            </button>

                            <button
                                v-else-if="!challenge.completed"
                                type="button"
                                class="w-full rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-400"
                                @click="completeChallenge(challenge)"
                            >
                                Complete challenge
                            </button>

                            <div
                                v-else
                                class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-5 py-3 text-center text-sm font-bold text-emerald-400"
                            >
                                Completed
                            </div>
                        </div>
                    </article>

                    <div
                        v-if="!challenges.length"
                        class="rounded-3xl border border-dashed border-zinc-800 p-10 text-center text-zinc-500"
                    >
                        No active challenges yet.
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>