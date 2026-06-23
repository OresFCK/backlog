<script setup>
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Search, Home, LayoutDashboard } from 'lucide-vue-next'

defineProps({
    auth: {
        type: Object,
        default: () => ({
            user: null,
        }),
    },
})

defineEmits(['write-review'])

const searchQuery = ref('')
const searchResults = ref([])
const isSearching = ref(false)
const showSearchResults = ref(false)

let searchTimeout = null

const searchGames = async () => {
    const query = searchQuery.value.trim()

    if (query.length < 2) {
        searchResults.value = []
        showSearchResults.value = false
        return
    }

    isSearching.value = true
    showSearchResults.value = true

    try {
        const response = await fetch(
            `/steam/search?q=${encodeURIComponent(query)}`
        )

        searchResults.value = await response.json()
    } catch {
        searchResults.value = []
    } finally {
        isSearching.value = false
    }
}

const goToGame = (game) => {
    searchQuery.value = ''
    searchResults.value = []
    showSearchResults.value = false

    const slug =
        game.slug ??
        String(game.title ?? game.name ?? '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')

    router.visit(`/${slug}`)
}

watch(searchQuery, () => {
    clearTimeout(searchTimeout)

    searchTimeout = setTimeout(() => {
        searchGames()
    }, 250)
})
</script>

<template>
    <header class="border-b border-zinc-800 bg-zinc-950/90 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center gap-6 px-6 py-5">
            <div class="flex items-center gap-6">
                <Link
                    href="/home"
                    class="text-xl font-black tracking-tight text-white"
                    aria-label="Curator.gg home"
                >
                    Curator.gg
                </Link>

                <nav class="flex items-center gap-2">
                    <Link
                        href="/home"
                        class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-zinc-400 transition hover:bg-zinc-900 hover:text-white"
                    >
                        <Home class="h-4 w-4" />
                        Home
                    </Link>

                    <Link
                        href="/dashboard"
                        class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-zinc-400 transition hover:bg-zinc-900 hover:text-white"
                    >
                        <LayoutDashboard class="h-4 w-4" />
                        Dashboard
                    </Link>
                </nav>
            </div>

            <div class="relative w-full max-w-xl flex-1">
                <Search
                    class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-500"
                />

                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search games..."
                    class="h-12 w-full rounded-2xl border border-zinc-800 bg-zinc-900 pl-12 pr-4 text-sm font-medium text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                    @focus="showSearchResults = searchResults.length > 0"
                >

                <div
                    v-if="showSearchResults"
                    class="absolute left-0 top-14 z-50 w-full overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950 shadow-2xl"
                >
                    <div
                        v-if="isSearching"
                        class="p-4 text-sm text-zinc-500"
                    >
                        Searching...
                    </div>

                    <button
                        v-for="game in searchResults"
                        :key="game.id ?? game.appid ?? game.steam_app_id"
                        type="button"
                        class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-zinc-900"
                        @click="goToGame(game)"
                    >
                        <img
                            v-if="game.cover_url"
                            :src="game.cover_url"
                            :alt="game.title ?? game.name"
                            class="h-12 w-9 rounded object-cover"
                        >

                        <div
                            v-else
                            class="h-12 w-9 rounded bg-zinc-800"
                        />

                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-white">
                                {{ game.title ?? game.name }}
                            </p>
                        </div>
                    </button>

                    <div
                        v-if="!isSearching && !searchResults.length"
                        class="p-4 text-sm text-zinc-500"
                    >
                        No games found.
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <Link
                    v-if="!auth.user"
                    href="/login"
                    class="rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-zinc-300 transition hover:bg-zinc-900 hover:text-white"
                >
                    Login
                </Link>

                <button
                    v-if="auth.user"
                    type="button"
                    class="rounded-xl bg-white px-4 py-2 text-sm font-black text-zinc-950 transition hover:bg-zinc-200"
                    @click="$emit('write-review')"
                >
                    Write review
                </button>
            </div>
        </div>
    </header>
</template>