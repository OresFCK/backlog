<script setup>
import { computed, ref, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

import GameSearchResults from '@/components/game/GameSearchResults.vue'

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
    game_id: '',
    game_title: '',
    game_cover_url: '',
    reward_xp: '',
    reward_coins: '',
    shop_item_id: '',
    is_active: true,
})

const rejectForm = useForm({
    admin_note: '',
})

const gameSearch = ref('')
const selectedGame = ref(null)

const steamResults = ref([])
const igdbResults = ref([])

const loadingSteam = ref(false)
const loadingIgdb = ref(false)

const loadingGames = computed(() => loadingSteam.value || loadingIgdb.value)

let gameSearchTimeout = null

watch(gameSearch, (value) => {
    clearTimeout(gameSearchTimeout)

    if (selectedGame.value && value === selectedGame.value.title) {
        return
    }

    selectedGame.value = null
    challengeForm.game_id = ''
    challengeForm.game_title = ''
    challengeForm.game_cover_url = ''

    if (!value || value.length < 2) {
        steamResults.value = []
        igdbResults.value = []
        return
    }

    gameSearchTimeout = setTimeout(async () => {
        loadingSteam.value = true
        loadingIgdb.value = true

        try {
            const [steamResponse, igdbResponse] = await Promise.all([
                fetch(`/steam/search?q=${encodeURIComponent(value)}`),
                fetch(`/igdb/search?q=${encodeURIComponent(value)}`),
            ])

            steamResults.value = steamResponse.ok
                ? await steamResponse.json()
                : []

            igdbResults.value = igdbResponse.ok
                ? await igdbResponse.json()
                : []
        } finally {
            loadingSteam.value = false
            loadingIgdb.value = false
        }
    }, 350)
})

const selectGame = (game) => {
    selectedGame.value = game
    gameSearch.value = game.title

    challengeForm.game_id = game.id ?? game.appid ?? game.steam_app_id ?? game.igdb_id ?? ''
    challengeForm.game_title = game.title
    challengeForm.game_cover_url = game.cover_url ?? game.igdb_cover_url ?? ''

    steamResults.value = []
    igdbResults.value = []
}

const createChallenge = () => {
    if (!challengeForm.game_id) {
        alert('Select a game from Steam or IGDB first.')
        return
    }

    challengeForm.post('/admin/challenges', {
        preserveScroll: true,

        onSuccess: () => {
            challengeForm.reset()
            challengeForm.reward_xp = ''
            challengeForm.reward_coins = ''
            challengeForm.shop_item_id = ''
            challengeForm.game_id = ''
            challengeForm.game_title = ''
            challengeForm.game_cover_url = ''
            challengeForm.is_active = true

            gameSearch.value = ''
            selectedGame.value = null
            steamResults.value = []
            igdbResults.value = []
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
    const note = prompt('Comment for accepted submission?') ?? ''

    router.post(`/admin/challenge-submissions/${submission.id}/approve`, {
        admin_note: note,
    }, {
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
    <section class="grid gap-8 xl:grid-cols-[520px_1fr]">
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

            <div class="space-y-3">
                <input
                    v-model="gameSearch"
                    placeholder="Search game, e.g. Elden Ring"
                    class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                />

                <div
                    v-if="selectedGame"
                    class="flex items-center gap-4 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-4"
                >
                    <img
                        v-if="selectedGame.cover_url || selectedGame.igdb_cover_url"
                        :src="selectedGame.cover_url || selectedGame.igdb_cover_url"
                        :alt="selectedGame.title"
                        class="h-16 w-28 shrink-0 rounded-xl object-cover"
                    />

                    <div
                        v-else
                        class="flex h-16 w-28 shrink-0 items-center justify-center rounded-xl bg-zinc-800 text-xs text-zinc-500"
                    >
                        GAME
                    </div>

                    <div class="min-w-0">
                        <p class="truncate font-semibold text-white">
                            {{ selectedGame.title }}
                        </p>

                        <p class="text-sm text-emerald-400">
                            Selected game
                        </p>
                    </div>
                </div>

                <p
                    v-if="!selectedGame && gameSearch.length >= 2 && !loadingGames && !steamResults.length && !igdbResults.length"
                    class="rounded-2xl border border-red-500/20 bg-red-500/10 p-3 text-sm text-red-300"
                >
                    No game found. You must select a game from Steam or IGDB.
                </p>

                <GameSearchResults
                    v-if="!selectedGame"
                    compact
                    :steam-results="steamResults"
                    :igdb-results="igdbResults"
                    :loading="loadingGames"
                    :duplicate="false"
                    @select-steam="selectGame"
                    @select-igdb="selectGame"
                />
            </div>

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
                class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="challengeForm.processing || !challengeForm.game_id"
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
                        class="grid gap-5 p-5 lg:grid-cols-[220px_1fr]"
                    >
                        <div class="grid gap-2">
                            <template v-if="submission.screenshot_urls?.length">
                                <a
                                    v-for="url in submission.screenshot_urls"
                                    :key="url"
                                    :href="url"
                                    target="_blank"
                                    class="block overflow-hidden rounded-2xl bg-zinc-800"
                                >
                                    <img
                                        :src="url"
                                        class="h-28 w-full object-cover"
                                    />
                                </a>
                            </template>

                            <a
                                v-else-if="submission.screenshot_url"
                                :href="submission.screenshot_url"
                                target="_blank"
                                class="block overflow-hidden rounded-2xl bg-zinc-800"
                            >
                                <img
                                    :src="submission.screenshot_url"
                                    class="h-32 w-full object-cover"
                                />
                            </a>
                        </div>

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

                            <div
                                v-if="submission.description"
                                class="mt-3 rounded-xl border border-zinc-700 bg-zinc-950 p-3 text-sm text-zinc-300"
                            >
                                <p class="mb-1 text-xs font-bold uppercase tracking-wide text-zinc-500">
                                    User description
                                </p>

                                {{ submission.description }}
                            </div>

                            <div
                                v-if="submission.admin_note"
                                class="mt-3 rounded-xl border p-3 text-sm"
                                :class="submission.status === 'approved'
                                    ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300'
                                    : 'border-red-500/20 bg-red-500/10 text-red-300'"
                            >
                                <p class="mb-1 text-xs font-bold uppercase tracking-wide opacity-70">
                                    Admin comment
                                </p>

                                {{ submission.admin_note }}
                            </div>

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
                                {{ challenge.game?.title ?? challenge.game_name }}
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