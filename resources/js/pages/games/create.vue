<script setup>
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'
import GameAddHeader from '@/components/game/GameAddHeader.vue'
import GameSearchResults from '@/components/game/GameSearchResults.vue'
import GameManualForm from '@/components/game/GameManualForm.vue'

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
const igdbResults = ref([])

const loadingSteam = ref(false)
const loadingIgdb = ref(false)

const normalizeTitle = (value) => {
    return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, ' ')
        .replace(/\b(the|game|edition|standard|deluxe|ultimate|complete)\b/g, '')
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

const loading = computed(() => loadingSteam.value || loadingIgdb.value)

let timeout = null

watch(title, (value) => {
    clearTimeout(timeout)

    steamAppId.value = null
    igdbId.value = null
    source.value = 'manual'

    if (!value || value.length < 2) {
        steamResults.value = []
        igdbResults.value = []
        return
    }

    timeout = setTimeout(async () => {
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

function selectSteamGame(game) {
    title.value = game.title
    coverUrl.value = game.cover_url
    headerImageUrl.value = game.header_image_url ?? game.cover_url
    steamAppId.value = game.appid
    igdbId.value = null
    source.value = 'steam'
}

function selectIgdbGame(game) {
    title.value = game.title
    coverUrl.value = game.igdb_cover_url ?? game.cover_url ?? ''
    headerImageUrl.value = game.igdb_cover_url ?? game.cover_url ?? ''
    steamAppId.value = null
    igdbId.value = game.igdb_id
    source.value = 'igdb'
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
                <GameAddHeader />

                <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
                    <GameSearchResults
                        :steam-results="steamResults"
                        :igdb-results="igdbResults"
                        :loading="loading"
                        :duplicate="duplicate"
                        @select-steam="selectSteamGame"
                        @select-igdb="selectIgdbGame"
                    >
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
                        </div>
                    </GameSearchResults>

                    <GameManualForm
                        v-model:title="title"
                        v-model:publisher="publisher"
                        v-model:cover-url="coverUrl"
                        :steam-app-id="steamAppId"
                        :igdb-id="igdbId"
                        :duplicate="duplicate"
                        @submit="submit"
                    />
                </div>
            </main>
        </div>
    </div>
</template>