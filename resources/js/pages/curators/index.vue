<script setup>
import { ref, watch } from 'vue'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import CuratorsHeroSearch from '@/components/curators/CuratorsHeroSearch.vue'
import CuratorsSearchResults from '@/components/curators/CuratorsSearchResults.vue'
import CuratorsGamePanel from '@/components/curators/CuratorsGamePanel.vue'

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
            `/curators/game/${game.source}/${game.id}?title=${encodeURIComponent(game.title)}`
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
                    <CuratorsHeroSearch v-model:query="query" />

                    <CuratorsSearchResults
                        :loading="loading"
                        :steam-results="steamResults"
                        :igdb-results="igdbResults"
                        @select-steam="selectSteam"
                        @select-igdb="selectIgdb"
                    />

                    <CuratorsGamePanel
                        v-if="selectedGame"
                        :game="selectedGame"
                        :stats="gameStats"
                        :loading="gameStatsLoading"
                    />
                </div>
            </main>
        </div>
    </div>
</template>