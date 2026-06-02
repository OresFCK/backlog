<script setup>
import { useForm, router } from '@inertiajs/vue3'

defineProps({
    shopItems: {
        type: Array,
        default: () => [],
    },

    challenges: {
        type: Array,
        default: () => [],
    },

    submissions: {
        type: Array,
        default: () => [],
    },
})

const challengeForm = useForm({
    title: '',
    description: '',
    game_name: '',
    reward_xp: 0,
    reward_coins: 0,
    shop_item_id: '',
    is_active: true,
})

const rejectForm = useForm({
    admin_note: '',
})

const createChallenge = () => {
    challengeForm.post('/admin/challenges', {
        preserveScroll: true,

        onSuccess: () => {
            challengeForm.reset()
            challengeForm.game_name = ''
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

const approveSubmission = (submission) => {
    router.post(`/admin/challenge-submissions/${submission.id}/approve`, {}, {
        preserveScroll: true,
    })
}

const rejectSubmission = (submission) => {
    const note = prompt('Reason for rejection?') ?? ''

    rejectForm.admin_note = note

    rejectForm.post(`/admin/challenge-submissions/${submission.id}/reject`, {
        preserveScroll: true,
        onSuccess: () => {
            rejectForm.reset()
        },
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

            <input
                v-model="challengeForm.title"
                placeholder="Challenge title"
                class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
            />

            <input
                v-model="challengeForm.game_name"
                placeholder="Game name, e.g. Elden Ring"
                class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
            />

            <textarea
                v-model="challengeForm.description"
                placeholder="Challenge description"
                rows="4"
                class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
            />

            <input
                v-model="challengeForm.reward_xp"
                type="number"
                min="0"
                placeholder="Reward XP"
                class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
            />

            <input
                v-model="challengeForm.reward_coins"
                type="number"
                min="0"
                placeholder="Reward coins"
                class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
            />

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

            <label class="flex items-center gap-3 text-sm text-zinc-300">
                <input
                    v-model="challengeForm.is_active"
                    type="checkbox"
                    class="h-4 w-4 rounded border-zinc-700 bg-zinc-950"
                />

                Active
            </label>

            <button
                type="submit"
                class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                :disabled="challengeForm.processing"
            >
                Create challenge
            </button>
        </form>

        <div class="space-y-8">
            <div class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900">
                <div class="border-b border-zinc-800 p-6">
                    <h2 class="text-xl font-bold text-white">
                        Challenge submissions
                    </h2>
                </div>

                <div class="divide-y divide-zinc-800">
                    <div
                        v-for="submission in submissions"
                        :key="submission.id"
                        class="grid gap-5 p-5 lg:grid-cols-[180px_1fr]"
                    >
                        <a
                            :href="submission.screenshot_url"
                            target="_blank"
                            class="block overflow-hidden rounded-2xl bg-zinc-800"
                        >
                            <img
                                :src="submission.screenshot_url"
                                class="h-32 w-full object-cover"
                            />
                        </a>

                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-bold text-white">
                                        {{ submission.challenge.title }}
                                    </h3>

                                    <p class="mt-1 text-sm text-zinc-400">
                                        {{ submission.challenge.game_name }}
                                    </p>

                                    <p class="mt-1 text-sm text-zinc-500">
                                        User: {{ submission.user.name }}
                                    </p>
                                </div>

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-bold"
                                    :class="{
                                        'bg-amber-500/10 text-amber-400': submission.status === 'pending',
                                        'bg-emerald-500/10 text-emerald-400': submission.status === 'approved',
                                        'bg-red-500/10 text-red-400': submission.status === 'rejected',
                                    }"
                                >
                                    {{ submission.status }}
                                </span>
                            </div>

                            <p class="mt-3 text-sm text-zinc-400">
                                Reward:
                                {{ submission.challenge.reward_xp }} XP ·
                                {{ submission.challenge.reward_coins }} coins
                            </p>

                            <p
                                v-if="submission.challenge.item"
                                class="mt-1 text-sm text-zinc-500"
                            >
                                Item: {{ submission.challenge.item.name }}
                            </p>

                            <p
                                v-if="submission.admin_note"
                                class="mt-3 rounded-xl border border-red-500/20 bg-red-500/10 p-3 text-sm text-red-300"
                            >
                                {{ submission.admin_note }}
                            </p>

                            <div
                                v-if="submission.status === 'pending'"
                                class="mt-4 flex gap-3"
                            >
                                <button
                                    type="button"
                                    class="rounded-xl bg-emerald-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-400"
                                    @click="approveSubmission(submission)"
                                >
                                    Approve
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl bg-red-500/10 px-4 py-2 text-sm font-bold text-red-400 transition hover:bg-red-500/20"
                                    @click="rejectSubmission(submission)"
                                >
                                    Reject
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="!submissions.length"
                        class="p-10 text-center text-zinc-500"
                    >
                        No submissions yet.
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900">
                <div class="border-b border-zinc-800 p-6">
                    <h2 class="text-xl font-bold text-white">
                        Challenges
                    </h2>
                </div>

                <div class="divide-y divide-zinc-800">
                    <div
                        v-for="challenge in challenges"
                        :key="challenge.id"
                        class="flex items-center gap-5 p-5"
                    >
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-white">
                                {{ challenge.title }}
                            </h3>

                            <p class="mt-1 text-sm text-emerald-400">
                                {{ challenge.game_name }}
                            </p>

                            <p class="mt-1 text-sm text-zinc-400">
                                {{ challenge.reward_xp }} XP ·
                                {{ challenge.reward_coins }} coins
                            </p>

                            <p
                                v-if="challenge.item"
                                class="mt-1 text-sm text-zinc-500"
                            >
                                Item: {{ challenge.item.name }}
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
        </div>
    </section>
</template>