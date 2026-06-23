<script setup>
import { computed, ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Grid3X3, List } from 'lucide-vue-next'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import GameGrid from '@/components/game/GameGrid.vue'
import GameList from '@/components/game/GameList.vue'
import RecommendationsSection from '@/components/recommendations/RecommendationsSection.vue'

const props = defineProps({
    user: Object,

    flash: {
        type: Object,
        default: () => ({}),
    },

    games: {
        type: Array,
        default: () => [],
    },

    statuses: {
        type: Array,
        default: () => [],
    },

    friendsRanking: {
        type: Array,
        default: () => [],
    },

    globalRanking: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()

const sortBy = ref('name')
const searchQuery = ref('')
const selectedStatus = ref('all')
const viewMode = ref('grid')
const showNoProductCardPopup = ref(false)

const selectionMode = ref(false)
const selectedGameIds = ref([])
const bulkStatus = ref('')

watch(
    () => page.props.flash?.no_product_card,
    (value) => {
        if (value) {
            showNoProductCardPopup.value = true
        }
    },
    { immediate: true }
)

const toggleSelectionMode = () => {
    selectionMode.value = !selectionMode.value

    if (!selectionMode.value) {
        selectedGameIds.value = []
        bulkStatus.value = ''
    }
}

const toggleGameSelection = (gameId) => {
    selectedGameIds.value = selectedGameIds.value.includes(gameId)
        ? selectedGameIds.value.filter((id) => id !== gameId)
        : [...selectedGameIds.value, gameId]
}

const clearSelection = () => {
    selectedGameIds.value = []
    bulkStatus.value = ''
    selectionMode.value = false
}

const updateBulkStatus = () => {
    if (!selectedGameIds.value.length || !bulkStatus.value) {
        return
    }

    router.post(
        '/games/bulk-status',
        {
            game_ids: selectedGameIds.value,
            status: bulkStatus.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => clearSelection(),
        }
    )
}

const mappedGames = computed(() => {
    let games = props.games.map((game) => {
        const status =
            props.statuses.find(
                (item) =>
                    item.name?.toLowerCase() ===
                    game.status?.toLowerCase()
            ) ?? null

        return {
            id: game.appid ?? game.id,

            title: game.name ?? game.title ?? 'Unknown game',

            cover_url:
                game.cover_url ??
                `https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/${game.appid}/library_600x900.jpg`,

            status: status?.name ?? 'Backlog',

            status_color: status?.color ?? '#71717a',

            average_playtime_minutes: game.playtime_forever ?? 0,

            platform: game.is_custom ? 'Custom' : 'Steam',

            rating: game.rating ?? null,
        }
    })

    if (searchQuery.value.trim()) {
        games = games.filter((game) =>
            String(game.title)
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase())
        )
    }

    if (selectedStatus.value !== 'all') {
        games = games.filter(
            (game) => game.status === selectedStatus.value
        )
    }

    switch (sortBy.value) {
        case 'playtime':
            return games.sort(
                (a, b) =>
                    b.average_playtime_minutes -
                    a.average_playtime_minutes
            )

        case 'rating':
            return games.sort(
                (a, b) => (b.rating ?? 0) - (a.rating ?? 0)
            )

        default:
            return games.sort((a, b) =>
                String(a.title).localeCompare(String(b.title))
            )
    }
})
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 space-y-10 p-8">
                <section
                    v-if="
                        friendsRanking?.length ||
                        globalRanking?.length
                    "
                >
                    <RecommendationsSection
                        :friends-ranking="friendsRanking"
                        :global-ranking="globalRanking"
                    />
                </section>

                <section>
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-white">
                                Your game library
                            </h2>

                            <p class="mt-1 text-zinc-400">
                                {{ mappedGames.length }}
                                games available
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-4">
                            <button
                                type="button"
                                class="rounded-xl border border-zinc-700 px-4 py-3 text-sm font-bold text-white transition hover:bg-zinc-900"
                                @click="toggleSelectionMode"
                            >
                                {{
                                    selectionMode
                                        ? 'Cancel'
                                        : 'Update statuses'
                                }}
                            </button>

                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search games..."
                                class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-500"
                            />

                            <select
                                v-model="sortBy"
                                class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                            >
                                <option value="name">
                                    Sort by name
                                </option>

                                <option value="playtime">
                                    Sort by playtime
                                </option>

                                <option value="rating">
                                    Sort by rating
                                </option>
                            </select>

                            <select
                                v-model="selectedStatus"
                                class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                            >
                                <option value="all">
                                    All statuses
                                </option>

                                <option
                                    v-for="status in statuses"
                                    :key="status.id"
                                    :value="status.name"
                                >
                                    {{ status.name }}
                                </option>
                            </select>

                            <div class="flex overflow-hidden rounded-xl border border-zinc-700">
                                <button
                                    type="button"
                                    class="flex items-center gap-2 px-4 py-3 text-sm font-bold transition"
                                    :class="
                                        viewMode === 'grid'
                                            ? 'bg-white text-zinc-950'
                                            : 'bg-zinc-900 text-zinc-400 hover:text-white'
                                    "
                                    @click="viewMode = 'grid'"
                                >
                                    <Grid3X3 class="h-4 w-4" />
                                    Grid
                                </button>

                                <button
                                    type="button"
                                    class="flex items-center gap-2 px-4 py-3 text-sm font-bold transition"
                                    :class="
                                        viewMode === 'list'
                                            ? 'bg-white text-zinc-950'
                                            : 'bg-zinc-900 text-zinc-400 hover:text-white'
                                    "
                                    @click="viewMode = 'list'"
                                >
                                    <List class="h-4 w-4" />
                                    List
                                </button>
                            </div>
                        </div>
                    </div>

                    <GameGrid
                        v-if="viewMode === 'grid'"
                        :games="mappedGames"
                        :selected-game-ids="selectedGameIds"
                        :selection-mode="selectionMode"
                        @toggle-game-selection="toggleGameSelection"
                    />

                    <GameList
                        v-else
                        :games="mappedGames"
                        :selected-game-ids="selectedGameIds"
                        :selection-mode="selectionMode"
                        @toggle-game-selection="toggleGameSelection"
                    />
                </section>
            </main>
        </div>

        <div
            v-if="selectionMode"
            class="fixed bottom-6 left-1/2 z-40 flex -translate-x-1/2 items-center gap-3 rounded-2xl border border-zinc-800 bg-zinc-900/95 px-4 py-3 shadow-2xl backdrop-blur"
        >
            <span class="text-sm font-bold text-white">
                {{ selectedGameIds.length }}
                selected
            </span>

            <select
                v-model="bulkStatus"
                class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white outline-none"
            >
                <option value="">
                    Status
                </option>

                <option
                    v-for="status in statuses"
                    :key="status.id"
                    :value="status.name"
                >
                    {{ status.name }}
                </option>
            </select>

            <button
                type="button"
                class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200 disabled:opacity-50"
                :disabled="!selectedGameIds.length || !bulkStatus"
                @click="updateBulkStatus"
            >
                Update
            </button>

            <button
                type="button"
                class="rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-zinc-800"
                @click="clearSelection"
            >
                Cancel
            </button>
        </div>

        <div
            v-if="showNoProductCardPopup"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6"
        >
            <div class="w-full max-w-md rounded-3xl border border-zinc-800 bg-zinc-900 p-6 text-center shadow-2xl">
                <h3 class="text-xl font-black text-white">
                    No product card
                </h3>

                <p class="mt-3 text-sm text-zinc-400">
                    Sadly this game has no product card so we can`t show you much :&lt;
                </p>

                <button
                    type="button"
                    class="mt-6 rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                    @click="showNoProductCardPopup = false"
                >
                    Okay
                </button>
            </div>
        </div>
    </div>
</template>