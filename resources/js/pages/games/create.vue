<script setup>
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },

    games: {
        type: Array,
        default: () => [],
    },
})

const title = ref('')
const publisher = ref('')
const coverUrl = ref('')
const headerImageUrl = ref('')
const steamAppId = ref(null)
const igdbId = ref(null)
const source = ref('manual')

const steamResults = ref([])
const loadingSteam = ref(false)

const normalizeTitle = (value) => {
    return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, ' ')
        .replace(/\b(the|game|edition|standard|deluxe|ultimate)\b/g, '')
        .trim()
        .replace(/\s+/g, ' ')
}

const duplicate = computed(() => {
    const current = normalizeTitle(title.value)

    if (!current) {
        return false
    }

    return props.games.some((game) => {
        const existingTitle = game.name ?? game.title ?? ''

        return normalizeTitle(existingTitle) === current
    })
})

let steamTimeout = null

watch(title, (value) => {
    clearTimeout(steamTimeout)

    steamAppId.value = null
    igdbId.value = null
    source.value = 'manual'

    if (!value || value.length < 2) {
        steamResults.value = []
        return
    }

    steamTimeout = setTimeout(async () => {
        loadingSteam.value = true

        try {
            const response = await fetch(
                `/steam/search?q=${encodeURIComponent(value)}`
            )

            steamResults.value = await response.json()
        } finally {
            loadingSteam.value = false
        }
    }, 350)
})

function selectSteamGame(game) {
    title.value = game.title
    coverUrl.value = game.cover_url
    headerImageUrl.value = game.header_image_url ?? game.cover_url
    steamAppId.value = game.appid
    igdbId.value = null
    source.value = 'steam'
}

function submit() {
    if (duplicate.value) {
        alert('This game already exists in your library.')
        return
    }

    router.post('/games', {
        title: title.value,
        publisher: publisher.value,
        cover_url: coverUrl.value,
        header_image_url: headerImageUrl.value,
        steam_app_id: steamAppId.value,
        igdb_id: igdbId.value,
        source: source.value,
    })
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="mx-auto w-full max-w-5xl flex-1 p-8">
                <div class="mb-10">
                    <h1 class="text-4xl font-bold text-white">
                        Add New Game
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Search Steam first. If the game is not there, add it manually.
                    </p>
                </div>

                <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
                    <section class="space-y-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-300">
                                Game title
                            </label>

                            <input
                                v-model="title"
                                type="text"
                                placeholder="e.g. Pokémon Shield"
                                class="w-full rounded-2xl border border-zinc-800 bg-zinc-900 px-5 py-4 text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                            />

                            <p
                                v-if="duplicate"
                                class="mt-3 text-sm font-medium text-red-400"
                            >
                                This game already exists in your library.
                            </p>
                        </div>

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
                                    class="flex w-full items-center gap-4 rounded-2xl border border-zinc-800 bg-zinc-950 p-3 text-left transition hover:border-zinc-600"
                                    @click="selectSteamGame(game)"
                                >
                                    <img
                                        :src="game.cover_url"
                                        :alt="game.title"
                                        class="h-16 w-28 rounded-xl object-cover"
                                    />

                                    <div>
                                        <p class="font-semibold text-white">
                                            {{ game.title }}
                                        </p>

                                        <p class="text-sm text-zinc-500">
                                            Steam App ID: {{ game.appid }}
                                        </p>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <p
                            v-if="loadingSteam"
                            class="text-sm text-zinc-500"
                        >
                            Searching Steam...
                        </p>
                    </section>

                    <aside class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-6">
                        <h2 class="text-xl font-bold text-white">
                            Manual details
                        </h2>

                        <div class="mt-6 space-y-5">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-300">
                                    Title
                                </label>

                                <input
                                    v-model="title"
                                    type="text"
                                    class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-white outline-none focus:border-zinc-600"
                                />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-300">
                                    Publisher
                                </label>

                                <input
                                    v-model="publisher"
                                    type="text"
                                    placeholder="e.g. Nintendo"
                                    class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                                />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-300">
                                    Cover URL
                                </label>

                                <input
                                    v-model="coverUrl"
                                    type="text"
                                    class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-white outline-none focus:border-zinc-600"
                                />
                            </div>

                            <img
                                v-if="coverUrl"
                                :src="coverUrl"
                                class="h-48 w-full rounded-2xl object-cover"
                            />

                            <div
                                v-if="steamAppId"
                                class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3 text-sm text-emerald-300"
                            >
                                Selected Steam game: {{ steamAppId }}
                            </div>

                            <button
                                type="button"
                                :disabled="duplicate || !title"
                                class="w-full rounded-xl bg-white px-5 py-3 text-sm font-bold text-black transition hover:bg-zinc-200 disabled:cursor-not-allowed disabled:opacity-40"
                                @click="submit"
                            >
                                Add game
                            </button>
                        </div>
                    </aside>
                </div>
            </main>
        </div>
    </div>
</template>