<script setup>
import { ref, watch } from 'vue'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

defineProps({
    user: Object,
})

const query = ref('')
const loading = ref(false)

const steamResults = ref([])
const igdbResults = ref([])

const selectedGame = ref(null)

const gameStats = ref(null)
const gameStatsLoading = ref(false)

let timeout = null

const searchGames = async () => {
    const value = query.value.trim()

    selectedGame.value = null
    gameStats.value = null

    if (value.length < 2) {
        steamResults.value = []
        igdbResults.value = []
        return
    }

    loading.value = true

    try {
        const [steamResponse, igdbResponse] = await Promise.all([
            fetch(`/steam/search?q=${encodeURIComponent(value)}`),
            fetch(`/igdb/search?q=${encodeURIComponent(value)}`),
        ])

        steamResults.value = await steamResponse.json()
        igdbResults.value = await igdbResponse.json()
    } catch (error) {
        console.error(error)

        steamResults.value = []
        igdbResults.value = []
    } finally {
        loading.value = false
    }
}

const loadGameStats = async (game) => {
    gameStatsLoading.value = true
    gameStats.value = null

    try {
        const response = await fetch(
            `/curators/game/${game.source}/${game.id}`
        )

        gameStats.value = await response.json()
    } catch (error) {
        console.error(error)

        gameStats.value = null
    } finally {
        gameStatsLoading.value = false
    }
}

watch(query, () => {
    clearTimeout(timeout)

    timeout = setTimeout(() => {
        searchGames()
    }, 350)
})

const selectSteam = async (game) => {
    selectedGame.value = {
        source: 'steam',
        id: game.appid,
        title: game.title,
        cover_url: game.cover_url,
    }

    steamResults.value = []
    igdbResults.value = []

    await loadGameStats(selectedGame.value)
}

const selectIgdb = async (game) => {
    selectedGame.value = {
        source: 'igdb',
        id: game.igdb_id,
        title: game.title,
        cover_url: game.igdb_cover_url || game.cover_url,
        release_date: game.release_date,
    }

    steamResults.value = []
    igdbResults.value = []

    await loadGameStats(selectedGame.value)
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 p-8">
                <div class="mx-auto max-w-7xl space-y-8">
                    <section class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-8">
                        <p class="text-sm font-bold uppercase tracking-[0.25em] text-zinc-500">
                            Community game pages
                        </p>

                        <h1 class="mt-3 text-4xl font-bold text-white">
                            Curators
                        </h1>

                        <p class="mt-3 max-w-2xl text-zinc-400">
                            Search a game and see community ratings, reviews and curator recommendations.
                        </p>

                        <div class="mt-8">
                            <label class="text-sm font-bold text-zinc-300">
                                Search game
                            </label>

                            <input
                                v-model="query"
                                type="text"
                                class="mt-2 w-full rounded-2xl border border-zinc-800 bg-zinc-950 px-5 py-4 text-white outline-none transition placeholder:text-zinc-600 focus:border-zinc-600"
                                placeholder="Search Steam or IGDB..."
                            >
                        </div>
                    </section>

                    <section class="space-y-6">
                        <p
                            v-if="loading"
                            class="text-sm text-zinc-500"
                        >
                            Searching Steam and IGDB...
                        </p>

                        <div
                            v-if="steamResults.length || igdbResults.length"
                            class="grid gap-6"
                            :class="steamResults.length && igdbResults.length ? 'xl:grid-cols-2' : 'grid-cols-1'"
                        >
                            <div
                                v-if="steamResults.length"
                                class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-5"
                            >
                                <h2 class="mb-4 text-lg font-bold text-white">
                                    Steam matches
                                </h2>

                                <div class="space-y-3">
                                    <button
                                        v-for="game in steamResults"
                                        :key="game.appid"
                                        type="button"
                                        class="w-full rounded-2xl border border-zinc-800 bg-zinc-950 p-4 text-left transition hover:border-zinc-600"
                                        @click="selectSteam(game)"
                                    >
                                        <div class="flex items-center gap-4">
                                            <img
                                                v-if="game.cover_url"
                                                :src="game.cover_url"
                                                :alt="game.title"
                                                class="h-16 w-28 rounded-xl object-cover"
                                            >

                                            <div
                                                v-else
                                                class="flex h-16 w-28 items-center justify-center rounded-xl bg-zinc-800 text-xs text-zinc-500"
                                            >
                                                Steam
                                            </div>

                                            <div>
                                                <p class="font-semibold text-white">
                                                    {{ game.title }}
                                                </p>

                                                <p class="text-sm text-zinc-500">
                                                    Steam App ID: {{ game.appid }}
                                                </p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <div
                                v-if="igdbResults.length"
                                class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-5"
                            >
                                <h2 class="mb-4 text-lg font-bold text-white">
                                    IGDB matches
                                </h2>

                                <div class="space-y-3">
                                    <button
                                        v-for="game in igdbResults"
                                        :key="game.igdb_id"
                                        type="button"
                                        class="w-full rounded-2xl border border-zinc-800 bg-zinc-950 p-4 text-left transition hover:border-zinc-600"
                                        @click="selectIgdb(game)"
                                    >
                                        <div class="flex items-center gap-4">
                                            <img
                                                v-if="game.igdb_cover_url || game.cover_url"
                                                :src="game.igdb_cover_url || game.cover_url"
                                                :alt="game.title"
                                                class="h-16 w-28 rounded-xl object-cover"
                                            >

                                            <div
                                                v-else
                                                class="flex h-16 w-28 items-center justify-center rounded-xl bg-zinc-800 text-xs text-zinc-500"
                                            >
                                                IGDB
                                            </div>

                                            <div>
                                                <p class="font-semibold text-white">
                                                    {{ game.title }}
                                                </p>

                                                <p class="text-sm text-zinc-500">
                                                    IGDB ID: {{ game.igdb_id }}
                                                </p>

                                                <p
                                                    v-if="game.release_date"
                                                    class="text-xs text-zinc-600"
                                                >
                                                    {{ game.release_date }}
                                                </p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="selectedGame"
                        class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-8"
                    >
                        <div class="flex flex-col gap-6 lg:flex-row">
                            <img
                                v-if="selectedGame.cover_url"
                                :src="selectedGame.cover_url"
                                :alt="selectedGame.title"
                                class="h-48 w-full rounded-2xl object-cover lg:w-80"
                            >

                            <div
                                v-else
                                class="flex h-48 w-full items-center justify-center rounded-2xl bg-zinc-800 text-zinc-500 lg:w-80"
                            >
                                No cover
                            </div>

                            <div class="flex-1">
                                <p class="text-xs font-bold uppercase tracking-[0.25em] text-zinc-500">
                                    {{ selectedGame.source }}
                                </p>

                                <h2 class="mt-2 text-3xl font-bold text-white">
                                    {{ selectedGame.title }}
                                </h2>

                                <p class="mt-2 text-sm text-zinc-500">
                                    Game ID: {{ selectedGame.id }}
                                </p>

                                <div class="mt-8 grid gap-4 md:grid-cols-3">
                                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
                                        <p class="text-sm text-zinc-500">
                                            Average rating
                                        </p>

                                        <p class="mt-2 text-3xl font-bold text-white">
                                            {{ gameStats?.average_rating ? `${gameStats.average_rating}/10` : '—' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
                                        <p class="text-sm text-zinc-500">
                                            Reviews
                                        </p>

                                        <p class="mt-2 text-3xl font-bold text-white">
                                            {{ gameStats?.reviews_count ?? '—' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
                                        <p class="text-sm text-zinc-500">
                                            Recommend
                                        </p>

                                        <p class="mt-2 text-3xl font-bold text-white">
                                            {{
                                                gameStats?.recommended_percent !== null && gameStats?.recommended_percent !== undefined
                                                    ? `${gameStats.recommended_percent}%`
                                                    : '—'
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <p
                                    v-if="gameStatsLoading"
                                    class="mt-6 text-sm text-zinc-500"
                                >
                                    Loading curator data...
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="gameStats?.reviews?.length"
                            class="mt-8 space-y-4"
                        >
                            <h3 class="text-xl font-bold text-white">
                                Community reviews
                            </h3>

                            <article
                                v-for="review in gameStats.reviews"
                                :key="review.id"
                                class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <img
                                            v-if="review.user?.avatar"
                                            :src="review.user.avatar"
                                            :alt="review.user.name"
                                            class="h-10 w-10 rounded-full object-cover"
                                        >

                                        <div
                                            v-else
                                            class="h-10 w-10 rounded-full bg-zinc-800"
                                        />

                                        <div>
                                            <p class="font-bold text-white">
                                                {{ review.user?.name ?? 'Unknown user' }}
                                            </p>

                                            <p class="text-xs text-zinc-500">
                                                {{ review.created_at }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-lg font-bold text-white">
                                            {{ review.rating }}/10
                                        </p>

                                        <p class="text-xs text-zinc-500">
                                            {{ review.recommended ? 'Recommended' : 'Not recommended' }}
                                        </p>
                                    </div>
                                </div>

                                <h4 class="mt-4 font-bold text-white">
                                    {{ review.title }}
                                </h4>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-zinc-300">
                                    {{ review.body }}
                                </p>
                            </article>
                        </div>

                        <div
                            v-else-if="gameStats && !gameStats.reviews?.length"
                            class="mt-8 rounded-2xl border border-dashed border-zinc-800 p-8 text-center text-zinc-500"
                        >
                            No community reviews for this game yet.
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>
</template>