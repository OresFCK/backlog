<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Search } from 'lucide-vue-next'

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

    const response = await fetch(
        `/steam/search?q=${encodeURIComponent(query)}`
    )

    searchResults.value = await response.json()
    isSearching.value = false
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
    <div class="min-h-screen bg-[#0b0b0d] text-white">
        <header
            class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-6"
        >
            <div class="shrink-0 text-lg font-bold">
                Curator.gg
            </div>

            <div class="relative w-full max-w-xl">
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

        <nav class="hidden shrink-0 gap-8 text-sm text-zinc-400 md:flex">
            <a
                href="#features"
                class="hover:text-white"
            >
                Features
            </a>

            <a
                href="#showcase"
                class="hover:text-white"
            >
                Showcase
            </a>

            <a
                href="#support"
                class="hover:text-white"
            >
                Support
            </a>
        </nav>
    </header>

        <main>
            <section class="mx-auto max-w-7xl px-6 py-24">
                <div class="flex flex-col items-center text-center">
                    <h1
                        class="max-w-4xl text-5xl font-bold tracking-tight md:text-7xl"
                    >
                        So many games,
                        <br>
                        so little time...
                    </h1>

                    <p
                        class="mt-6 max-w-2xl text-lg leading-8 text-zinc-400"
                    >
                        Connect your Steam account, organize your library,
                        discover what to play next and build your gaming profile.
                    </p>

                    <a
                        href="/auth/steam"
                        class="mt-10 rounded-2xl bg-white px-8 py-4 text-sm font-semibold text-black transition hover:bg-zinc-200"
                    >
                        Login with Steam
                    </a>

                    <p class="mt-6 max-w-xl text-sm text-zinc-500">
                        By signing in with Steam, you agree to our
                        <a
                            href="/terms"
                            class="underline transition hover:text-zinc-300"
                        >
                            Terms of Service
                        </a>
                        and
                        <a
                            href="/privacy"
                            class="underline transition hover:text-zinc-300"
                        >
                            Privacy Policy
                        </a>.
                    </p>
                </div>

                <div
                    class="mt-20 overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900 shadow-2xl"
                >
                    <img
                        src="/dashboard.png"
                        alt="Dashboard"
                        class="w-full"
                    >
                </div>
            </section>

            <section
                id="features"
                class="mx-auto max-w-7xl px-6 pb-24"
            >
                <div class="grid gap-4 md:grid-cols-4">
                    <div
                        class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6"
                    >
                        <h3 class="font-semibold">
                            Steam synchronization
                        </h3>

                        <p class="mt-3 text-sm text-zinc-500">
                            Import your games, achievements, playtime and profile data automatically.
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6"
                    >
                        <h3 class="font-semibold">
                            Library management
                        </h3>

                        <p class="mt-3 text-sm text-zinc-500">
                            Organize your backlog, completed titles and currently played games.
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6"
                    >
                        <h3 class="font-semibold">
                            Curator profile
                        </h3>

                        <p class="mt-3 text-sm text-zinc-500">
                            Build a public gaming identity and showcase your collection.
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6"
                    >
                        <h3 class="font-semibold">
                            Community reviews
                        </h3>

                        <p class="mt-3 text-sm text-zinc-500">
                            Discover recommendations and opinions from other players.
                        </p>
                    </div>
                </div>
            </section>

            <section
                id="showcase"
                class="bg-zinc-950 py-24"
            >
                <div class="mx-auto max-w-7xl px-6">
                    <h2
                        class="text-center text-4xl font-bold md:text-5xl"
                    >
                        See it. Track it. Own it.
                    </h2>

                    <div class="mt-20 grid gap-12 lg:grid-cols-2">
                        <div class="flex flex-col">
                            <div
                                class="aspect-[16/8] overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900 shadow-[0_0_60px_rgba(0,0,0,0.4)]"
                            >
                                <img
                                    src="/profil_banner.png"
                                    alt="Profile"
                                    class="h-full w-full object-cover object-center"
                                >
                            </div>

                            <h3 class="mt-8 text-2xl font-semibold">
                                Your gaming identity
                            </h3>

                            <p class="mt-4 text-zinc-500">
                                Create a profile that showcases your collection,
                                achievements and activity.
                            </p>
                        </div>

                        <div class="flex flex-col">
                            <div
                                class="aspect-[16/8] overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900 shadow-[0_0_60px_rgba(0,0,0,0.4)]"
                            >
                                <img
                                    src="/library.png"
                                    alt="Library"
                                    class="h-full w-full object-cover object-top"
                                >
                            </div>

                            <h3 class="mt-8 text-2xl font-semibold">
                                Organize your entire library
                            </h3>

                            <p class="mt-4 text-zinc-500">
                                Search, filter and categorize hundreds of games effortlessly.
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-24 overflow-hidden rounded-3xl border border-zinc-800 shadow-[0_0_60px_rgba(0,0,0,0.4)]"
                    >
                        <img
                            src="/reviews.png"
                            alt="Reviews"
                            class="w-full"
                        >
                    </div>

                    <div class="mt-8 text-center">
                        <h3 class="text-2xl font-semibold">
                            Community powered reviews
                        </h3>

                        <p
                            class="mx-auto mt-4 max-w-2xl text-zinc-500"
                        >
                            Share recommendations, discover hidden gems and see
                            what other players think.
                        </p>
                    </div>
                </div>
            </section>

            <section
                id="support"
                class="mx-auto max-w-6xl px-6 py-24 text-center"
            >
                <h2 class="text-4xl font-bold">
                    Curator
                    <span class="text-zinc-400">
                        — for those who want to see us grow
                    </span>
                </h2>

                <p
                    class="mx-auto mt-6 max-w-2xl text-zinc-500"
                >
                    Support development and unlock extra features while helping
                    Curator.gg become the ultimate Steam companion.
                </p>

                <a
                    href="/auth/steam"
                    class="mt-10 inline-flex rounded-2xl bg-purple-600 px-8 py-4 text-sm font-semibold transition hover:bg-purple-500"
                >
                    Become a supporter
                </a>

                <div
                    class="mx-auto mt-16 grid max-w-xl gap-3 text-left"
                >
                    <div class="rounded-xl border border-zinc-800 bg-zinc-900 px-5 py-4">
                        Advanced statistics
                    </div>

                    <div class="rounded-xl border border-zinc-800 bg-zinc-900 px-5 py-4">
                        Custom game statuses
                    </div>

                    <div class="rounded-xl border border-zinc-800 bg-zinc-900 px-5 py-4">
                        Profile customization
                    </div>

                    <div class="rounded-xl border border-zinc-800 bg-zinc-900 px-5 py-4">
                        Exclusive supporter badge
                    </div>

                    <div class="rounded-xl border border-zinc-800 bg-zinc-900 px-5 py-4">
                        Early access to new features
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-zinc-800">
            <div
                class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 py-8 text-sm text-zinc-500 md:flex-row"
            >
                <div>
                    © 2025 Curator.gg
                </div>

                <div class="flex items-center gap-6">
                    <a
                        href="/terms"
                        class="transition hover:text-white"
                    >
                        Terms of Service
                    </a>

                    <a
                        href="/privacy"
                        class="transition hover:text-white"
                    >
                        Privacy Policy
                    </a>
                </div>
            </div>
        </footer>
    </div>
</template>