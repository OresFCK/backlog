<script setup>
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    shopItems: {
        type: Array,
        default: () => [],
    },

    challenges: {
        type: Array,
        default: () => [],
    },
})

const challengeForm = useForm({
    title: '',
    description: '',
    reward_xp: 0,
    reward_coins: 0,
    shop_item_id: '',
    is_active: true,
})

const createChallenge = () => {
    challengeForm.post('/admin/challenges', {
        preserveScroll: true,

        onSuccess: () => {
            challengeForm.reset()

            challengeForm.reward_xp = 0
            challengeForm.reward_coins = 0
            challengeForm.shop_item_id = ''
            challengeForm.is_active = true
        },
    })
}

const deleteChallenge = (challenge) => {
    if (!confirm(`Delete "${challenge.title}"?`)) {
        return
    }

    router.delete(`/admin/challenges/${challenge.id}`, {
        preserveScroll: true,
    })
}
</script>

<template>
    <section class="grid gap-8 xl:grid-cols-[420px_1fr]">
        <form
            class="space-y-5 rounded-3xl border border-zinc-800 bg-zinc-900 p-6"
            @submit.prevent="createChallenge"
        >
            <h2 class="text-xl font-bold text-white">
                Challenge creator
            </h2>

            <div
                class="rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-4"
            >
                <h3 class="mb-3 text-sm font-bold text-emerald-300">
                    Challenge fields
                </h3>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-zinc-400">
                            Title
                        </span>

                        <span class="text-right font-medium text-white">
                            Name shown to users
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-zinc-400">
                            Description
                        </span>

                        <span class="text-right font-medium text-white">
                            Explains what users need to do
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-zinc-400">
                            XP reward
                        </span>

                        <span class="text-right font-medium text-white">
                            Experience granted after completion
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-zinc-400">
                            Coins reward
                        </span>

                        <span class="text-right font-medium text-white">
                            Shop currency granted after completion
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-zinc-400">
                            Item reward
                        </span>

                        <span class="text-right font-medium text-white">
                            Optional shop item reward
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-zinc-400">
                            Active
                        </span>

                        <span class="text-right font-medium text-white">
                            Visible and available to players
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <label
                    class="mb-2 block text-sm font-bold text-white"
                >
                    Challenge title
                </label>

                <p
                    class="mb-3 text-xs text-zinc-500"
                >
                    Public name displayed in the Challenges section.
                </p>

                <input
                    v-model="challengeForm.title"
                    placeholder="Finish 5 games"
                    class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                />
            </div>

            <div>
                <label
                    class="mb-2 block text-sm font-bold text-white"
                >
                    Challenge description
                </label>

                <p
                    class="mb-3 text-xs text-zinc-500"
                >
                    Explain exactly how the challenge can be completed.
                </p>

                <textarea
                    v-model="challengeForm.description"
                    rows="4"
                    placeholder="Complete and finish 5 games from your backlog."
                    class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                />
            </div>

            <div>
                <label
                    class="mb-2 block text-sm font-bold text-white"
                >
                    Reward XP
                </label>

                <p
                    class="mb-3 text-xs text-zinc-500"
                >
                    Experience points awarded after completion. Can increase player level.
                </p>

                <input
                    v-model="challengeForm.reward_xp"
                    type="number"
                    min="0"
                    placeholder="250"
                    class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                />
            </div>

            <div>
                <label
                    class="mb-2 block text-sm font-bold text-white"
                >
                    Reward coins
                </label>

                <p
                    class="mb-3 text-xs text-zinc-500"
                >
                    Currency added directly to the user's wallet.
                </p>

                <input
                    v-model="challengeForm.reward_coins"
                    type="number"
                    min="0"
                    placeholder="500"
                    class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                />
            </div>

            <div>
                <label
                    class="mb-2 block text-sm font-bold text-white"
                >
                    Reward item
                </label>

                <p
                    class="mb-3 text-xs text-zinc-500"
                >
                    Optional cosmetic or shop item granted on completion.
                </p>

                <select
                    v-model="challengeForm.shop_item_id"
                    class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                >
                    <option value="">
                        No item reward
                    </option>

                    <option
                        v-for="item in shopItems"
                        :key="item.id"
                        :value="item.id"
                    >
                        {{ item.name }} · {{ item.type }}
                    </option>
                </select>
            </div>

            <label
                class="flex items-center gap-3 text-sm text-zinc-300"
            >
                <input
                    v-model="challengeForm.is_active"
                    type="checkbox"
                    class="h-4 w-4 rounded border-zinc-700 bg-zinc-950"
                >

                <span>
                    Active — visible and available for users
                </span>
            </label>

            <button
                type="submit"
                class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                :disabled="challengeForm.processing"
            >
                Create challenge
            </button>
        </form>

        <div
            class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900"
        >
            <div
                class="border-b border-zinc-800 p-6"
            >
                <h2
                    class="text-xl font-bold text-white"
                >
                    Challenges
                </h2>
            </div>

            <div
                class="divide-y divide-zinc-800"
            >
                <div
                    v-for="challenge in challenges"
                    :key="challenge.id"
                    class="flex items-center gap-5 p-5"
                >
                    <div class="min-w-0 flex-1">
                        <h3
                            class="font-bold text-white"
                        >
                            {{ challenge.title }}
                        </h3>

                        <p
                            class="mt-1 text-sm text-zinc-400"
                        >
                            {{ challenge.reward_xp }} XP ·
                            {{ challenge.reward_coins }} coins
                        </p>

                        <p
                            v-if="challenge.item"
                            class="mt-1 text-sm text-zinc-500"
                        >
                            Item: {{ challenge.item.name }}
                        </p>

                        <p
                            v-if="challenge.description"
                            class="mt-1 text-sm text-zinc-500"
                        >
                            {{ challenge.description }}
                        </p>
                    </div>

                    <span
                        class="rounded-full px-3 py-1 text-xs font-bold"
                        :class="
                            challenge.is_active
                                ? 'bg-emerald-500/10 text-emerald-400'
                                : 'bg-zinc-800 text-zinc-500'
                        "
                    >
                        {{ challenge.is_active ? 'Active' : 'Hidden' }}
                    </span>

                    <button
                        type="button"
                        class="rounded-xl bg-red-500/10 px-4 py-2 text-sm font-bold text-red-400 transition hover:bg-red-500/20"
                        @click="deleteChallenge(challenge)"
                    >
                        Delete
                    </button>
                </div>

                <div
                    v-if="!challenges.length"
                    class="p-10 text-center text-zinc-500"
                >
                    No challenges yet.
                </div>
            </div>
        </div>
    </section>
</template>