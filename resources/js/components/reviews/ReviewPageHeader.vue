<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Search, Home, LayoutDashboard } from 'lucide-vue-next';

defineProps({
    auth: {
        type: Object,
        default: () => ({
            user: null,
        }),
    },
});

defineEmits(['write-review']);

const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const showSearchResults = ref(false);

let searchTimeout = null;

const searchGames = async () => {
    const query = searchQuery.value.trim();

    if (query.length < 2) {
        searchResults.value = [];
        showSearchResults.value = false;
        return;
    }

    isSearching.value = true;
    showSearchResults.value = true;

    try {
        const response = await fetch(
            `/steam/search?q=${encodeURIComponent(query)}`,
        );

        searchResults.value = await response.json();
    } catch {
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
};

const goToGame = (game) => {
    searchQuery.value = '';
    searchResults.value = [];
    showSearchResults.value = false;

    const slug =
        game.slug ??
        String(game.title ?? game.name ?? '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');

    router.visit(`/${slug}`);
};

watch(searchQuery, () => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        searchGames();
    }, 250);
});
</script>

<template>
    <header class="border-b border-zinc-800 bg-zinc-950/90 backdrop-blur">
        <div
            class="mx-auto grid max-w-7xl grid-cols-[1fr_auto] items-center gap-3 px-4 py-3 sm:px-6 sm:py-5 lg:flex lg:gap-6"
        >
            <div class="flex min-w-0 items-center gap-2 sm:gap-6">
                <Link
                    href="/home"
                    class="shrink-0 text-lg font-black tracking-tight text-white sm:text-xl"
                    aria-label="Curator.gg home"
                >
                    Curator.gg
                </Link>

                <nav class="flex items-center gap-1 sm:gap-2">
                    <Link
                        href="/home"
                        class="flex items-center gap-2 rounded-xl p-2 text-sm font-medium text-zinc-400 transition hover:bg-zinc-900 hover:text-white sm:px-3"
                    >
                        <Home class="h-4 w-4" />
                        <span class="hidden sm:inline">Home</span>
                    </Link>

                    <Link
                        href="/dashboard"
                        class="flex items-center gap-2 rounded-xl p-2 text-sm font-medium text-zinc-400 transition hover:bg-zinc-900 hover:text-white sm:px-3"
                    >
                        <LayoutDashboard class="h-4 w-4" />
                        <span class="hidden sm:inline">Dashboard</span>
                    </Link>
                </nav>
            </div>

            <div
                class="relative col-span-2 row-start-2 w-full lg:col-span-1 lg:row-auto lg:max-w-xl lg:flex-1"
            >
                <Search
                    class="pointer-events-none absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-zinc-500"
                />

                <input
                    v-model="searchQuery"
                    type="text"
                    maxlength="100"
                    placeholder="Search games..."
                    class="h-11 w-full rounded-xl border border-zinc-800 bg-zinc-900 pr-4 pl-12 text-sm font-medium text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600 sm:h-12 sm:rounded-2xl"
                    @focus="showSearchResults = searchResults.length > 0"
                />

                <div
                    v-if="showSearchResults"
                    class="absolute top-12 left-0 z-50 max-h-[60vh] w-full overflow-y-auto rounded-2xl border border-zinc-800 bg-zinc-950 shadow-2xl sm:top-14"
                >
                    <div v-if="isSearching" class="p-4 text-sm text-zinc-500">
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
                        />

                        <div v-else class="h-12 w-9 rounded bg-zinc-800" />

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

            <div class="flex items-center justify-end gap-2 sm:gap-3">
                <Link
                    v-if="!auth.user"
                    href="/login"
                    class="rounded-xl border border-zinc-700 px-3 py-2 text-sm font-bold text-zinc-300 transition hover:bg-zinc-900 hover:text-white sm:px-4"
                >
                    Login
                </Link>

                <button
                    v-if="auth.user"
                    type="button"
                    class="rounded-xl bg-white px-3 py-2 text-sm font-black text-zinc-950 transition hover:bg-zinc-200 sm:px-4"
                    @click="$emit('write-review')"
                >
                    Write review
                </button>
            </div>
        </div>
    </header>
</template>
