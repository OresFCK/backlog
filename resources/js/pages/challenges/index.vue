<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

const props = defineProps({
    user: Object,

    challenges: {
        type: Array,
        default: () => [],
    },
})

const selectedChallenge = ref(null)
const challengeFilter = ref('open')

const filteredChallenges = computed(() => {
    switch (challengeFilter.value) {
        case 'joined':
            return props.challenges.filter(
                (challenge) =>
                    challenge.joined &&
                    !challenge.completed &&
                    challenge.submission_status !== 'pending'
            )

        case 'pending':
            return props.challenges.filter(
                (challenge) => challenge.submission_status === 'pending'
            )

        case 'completed':
            return props.challenges.filter(
                (challenge) => challenge.completed
            )

        case 'all':
            return props.challenges

        case 'open':
        default:
            return props.challenges.filter(
                (challenge) =>
                    !challenge.joined ||
                    (
                        challenge.joined &&
                        !challenge.completed &&
                        challenge.submission_status !== 'pending'
                    )
            )
    }
})

const counts = computed(() => ({
    open: props.challenges.filter(
        (challenge) =>
            !challenge.joined ||
            (
                challenge.joined &&
                !challenge.completed &&
                challenge.submission_status !== 'pending'
            )
    ).length,

    joined: props.challenges.filter(
        (challenge) =>
            challenge.joined &&
            !challenge.completed &&
            challenge.submission_status !== 'pending'
    ).length,

    pending: props.challenges.filter(
        (challenge) => challenge.submission_status === 'pending'
    ).length,

    completed: props.challenges.filter(
        (challenge) => challenge.completed
    ).length,

    all: props.challenges.length,
}))

const proofForm = useForm({
    screenshots: [],
    description: '',
})

const joinChallenge = (challenge) => {
    router.post(`/challenges/${challenge.id}/join`, {}, {
        preserveScroll: true,
    })
}

const openProofModal = (challenge) => {
    selectedChallenge.value = challenge
    proofForm.clearErrors()
}

const closeProofModal = () => {
    selectedChallenge.value = null
    proofForm.clearErrors()
}

const resetProofForm = () => {
    proofForm.clearErrors()
    proofForm.reset()
    proofForm.screenshots = []
    proofForm.description = ''
}

const handleScreenshots = (event) => {
    proofForm.screenshots = Array.from(event.target.files ?? []).slice(0, 5)

    event.target.value = ''
}

const submitProof = () => {
    if (!selectedChallenge.value) {
        return
    }

    proofForm.post(`/challenges/${selectedChallenge.value.id}/submit`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            closeProofModal()
            resetProofForm()
        },
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
                        Join challenges, submit proof and earn XP, coins or shop items after admin approval.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button
                            type="button"
                            class="rounded-2xl px-4 py-2 text-sm font-bold transition"
                            :class="challengeFilter === 'open'
                                ? 'bg-white text-zinc-950'
                                : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700'"
                            @click="challengeFilter = 'open'"
                        >
                            Open ({{ counts.open }})
                        </button>

                        <button
                            type="button"
                            class="rounded-2xl px-4 py-2 text-sm font-bold transition"
                            :class="challengeFilter === 'joined'
                                ? 'bg-white text-zinc-950'
                                : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700'"
                            @click="challengeFilter = 'joined'"
                        >
                            Joined ({{ counts.joined }})
                        </button>

                        <button
                            type="button"
                            class="rounded-2xl px-4 py-2 text-sm font-bold transition"
                            :class="challengeFilter === 'pending'
                                ? 'bg-white text-zinc-950'
                                : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700'"
                            @click="challengeFilter = 'pending'"
                        >
                            Pending ({{ counts.pending }})
                        </button>

                        <button
                            type="button"
                            class="rounded-2xl px-4 py-2 text-sm font-bold transition"
                            :class="challengeFilter === 'completed'
                                ? 'bg-white text-zinc-950'
                                : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700'"
                            @click="challengeFilter = 'completed'"
                        >
                            Completed ({{ counts.completed }})
                        </button>

                        <button
                            type="button"
                            class="rounded-2xl px-4 py-2 text-sm font-bold transition"
                            :class="challengeFilter === 'all'
                                ? 'bg-white text-zinc-950'
                                : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700'"
                            @click="challengeFilter = 'all'"
                        >
                            All ({{ counts.all }})
                        </button>
                    </div>
                </section>

                <section class="grid gap-6 lg:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="challenge in filteredChallenges"
                        :key="challenge.id"
                        class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6"
                    >
                        <div class="mb-4">
                            <p
                                v-if="challenge.game_name"
                                class="mb-2 inline-flex rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-400"
                            >
                                {{ challenge.game_name }}
                            </p>

                            <h2 class="text-xl font-bold text-white">
                                {{ challenge.title }}
                            </h2>
                        </div>

                        <p class="text-sm text-zinc-400">
                            {{ challenge.description }}
                        </p>

                        <div
                            v-if="challenge.admin_note && challenge.submission_status === 'rejected'"
                            class="mt-4 rounded-2xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-300"
                        >
                            {{ challenge.admin_note }}
                        </div>

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
                                v-else-if="!challenge.completed && challenge.submission_status !== 'pending'"
                                type="button"
                                class="w-full rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-400"
                                @click="openProofModal(challenge)"
                            >
                                Submit proof
                            </button>

                            <div
                                v-else-if="challenge.submission_status === 'pending'"
                                class="rounded-2xl border border-amber-500/20 bg-amber-500/10 px-5 py-3 text-center text-sm font-bold text-amber-400"
                            >
                                Waiting for admin review
                            </div>

                            <div
                                v-else
                                class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-5 py-3 text-center text-sm font-bold text-emerald-400"
                            >
                                <template v-if="challenge.admin_note">
                                    {{ challenge.admin_note }}
                                </template>

                                <template v-else>
                                    Completed
                                </template>
                            </div>
                        </div>
                    </article>

                    <div
                        v-if="!filteredChallenges.length"
                        class="rounded-3xl border border-dashed border-zinc-800 p-10 text-center text-zinc-500"
                    >
                        No challenges found for this filter.
                    </div>
                </section>
            </main>
        </div>

        <div
            v-if="selectedChallenge"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6"
            @click.self="closeProofModal"
        >
            <form
                class="w-full max-w-lg rounded-3xl border border-zinc-800 bg-zinc-950 p-6"
                @submit.prevent="submitProof"
            >
                <h2 class="text-xl font-bold text-white">
                    Submit proof
                </h2>

                <p class="mt-2 text-sm text-zinc-400">
                    Upload screenshots proving you completed:
                    <span class="font-bold text-white">
                        {{ selectedChallenge.title }}
                    </span>
                </p>

                <label
                    class="mt-6 flex w-full cursor-pointer items-center gap-4 rounded-2xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white"
                >
                    <span class="shrink-0 rounded-xl bg-white px-4 py-2 text-sm font-bold text-zinc-950">
                        Choose files
                    </span>

                    <span class="truncate text-zinc-300">
                        <template v-if="proofForm.screenshots.length">
                            {{
                                proofForm.screenshots
                                    .map((file) => file.name)
                                    .join(', ')
                            }}
                        </template>

                        <template v-else>
                            No files selected
                        </template>
                    </span>

                    <input
                        type="file"
                        multiple
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                        @change="handleScreenshots"
                    />
                </label>

                <p class="mt-2 text-xs text-zinc-500">
                    You can upload up to 5 screenshots.
                </p>

                <p
                    v-if="proofForm.errors.screenshots"
                    class="mt-2 text-sm text-red-400"
                >
                    {{ proofForm.errors.screenshots }}
                </p>

                <p
                    v-if="proofForm.errors['screenshots.0']"
                    class="mt-2 text-sm text-red-400"
                >
                    {{ proofForm.errors['screenshots.0'] }}
                </p>

                <textarea
                    v-model="proofForm.description"
                    rows="4"
                    maxlength="1000"
                    placeholder="Add a short description for your submission..."
                    class="mt-4 w-full rounded-2xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                />

                <p
                    v-if="proofForm.errors.description"
                    class="mt-2 text-sm text-red-400"
                >
                    {{ proofForm.errors.description }}
                </p>

                <div
                    v-if="proofForm.screenshots.length"
                    class="mt-3 text-xs text-zinc-400"
                >
                    Selected files: {{ proofForm.screenshots.length }} / 5
                </div>

                <div class="mt-6 flex gap-3">
                    <button
                        type="submit"
                        class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="proofForm.processing || !proofForm.screenshots.length"
                    >
                        Send to admin
                    </button>

                    <button
                        type="button"
                        class="rounded-2xl bg-zinc-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-zinc-700"
                        @click="closeProofModal"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>