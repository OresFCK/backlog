<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { normalizeStatus, ratingStars, statusColor } from '@/lib/profile'

const props = defineProps({
    games: {
        type: Array,
        default: () => [],
    },

    groupedGames: {
        type: Object,
        default: () => ({}),
    },
})

const searchQuery = ref('')
const selectedStatus = ref('all')
const selectedRating = ref('all')
const openStatuses = ref({})

const statuses = computed(() =>
    Object.keys(props.groupedGames ?? {})
)

const gameUrl = (game) => {
    const id =
        game.id ??
        game.appid ??
        game.steam_app_id ??
        game.database_id

    return `/games/${id}`
}

const filteredGames = computed(() => {
    return props.games.filter((game) => {
        const title = String(game.title ?? game.name ?? '')
            .toLowerCase()

        const status = normalizeStatus(game.status)

        const matchesSearch =
            !searchQuery.value.trim() ||
            title.includes(searchQuery.value.toLowerCase())

        const matchesStatus =
            selectedStatus.value === 'all' ||
            status === selectedStatus.value

        const matchesRating =
            selectedRating.value === 'all' ||
            Number(game.rating ?? 0) >= Number(selectedRating.value)

        return matchesSearch && matchesStatus && matchesRating
    })
})

const filteredGroupedGames = computed(() => {
    return filteredGames.value.reduce((groups, game) => {
        const status = normalizeStatus(game.status)

        if (!groups[status]) {
            groups[status] = []
        }

        groups[status].push(game)

        return groups
    }, {})
})

const toggleStatus = (status) => {
    openStatuses.value[status] = !openStatuses.value[status]
}

const clearFilters = () => {
    searchQuery.value = ''
    selectedStatus.value = 'all'
    selectedRating.value = 'all'
}
</script>

<template>
    <div class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold">
                    Games Library
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    {{ filteredGames.length }} of {{ games.length }} games
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search games..."
                    class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-500"
                />

                <select
                    v-model="selectedRating"
                    class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                >
                    <option value="all">
                        All ratings
                    </option>

                    <option value="10">
                        10★ only
                    </option>

                    <option value="9">
                        9★+
                    </option>

                    <option value="8">
                        8★+
                    </option>

                    <option value="7">
                        7★+
                    </option>

                    <option value="6">
                        6★+
                    </option>

                    <option value="5">
                        5★+
                    </option>

                    <option value="4">
                        4★+
                    </option>

                    <option value="3">
                        3★+
                    </option>

                    <option value="2">
                        2★+
                    </option>

                    <option value="1">
                        1★+
                    </option>
                </select>

                <button
                    v-if="
                        searchQuery ||
                        selectedStatus !== 'all' ||
                        selectedRating !== 'all'
                    "
                    type="button"
                    class="rounded-xl border border-zinc-700 px-4 py-3 text-sm font-bold text-white transition hover:bg-zinc-800"
                    @click="clearFilters"
                >
                    Clear
                </button>
            </div>
        </div>

        <div
            v-if="filteredGames.length"
            class="space-y-4"
        >
            <div
                v-for="(statusGames, status) in filteredGroupedGames"
                :key="status"
                class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950"
            >
                <button
                    type="button"
                    class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-zinc-900"
                    @click="toggleStatus(status)"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="h-3 w-3 rounded-full"
                            :style="{ backgroundColor: statusColor(statusGames[0]) }"
                        />

                        <div>
                            <h3 class="font-semibold text-white">
                                {{ status }}
                            </h3>

                            <p class="text-sm text-zinc-500">
                                {{ statusGames.length }} games
                            </p>
                        </div>
                    </div>

                    <span class="text-xl text-zinc-500">
                        {{ openStatuses[status] ? '−' : '+' }}
                    </span>
                </button>

                <div
                    v-if="openStatuses[status]"
                    class="grid gap-4 border-t border-zinc-800 p-4 md:grid-cols-2 2xl:grid-cols-3"
                >
                    <Link
                        v-for="game in statusGames"
                        :key="game.id ?? game.appid ?? game.steam_app_id ?? game.database_id"
                        :href="gameUrl(game)"
                        class="block overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 transition hover:border-zinc-600 hover:bg-zinc-800"
                    >
                        <img
                            v-if="game.cover_url"
                            :src="game.cover_url"
                            class="h-48 w-full object-cover"
                        />

                        <div class="p-4">
                            <h3 class="truncate font-semibold text-white">
                                {{ game.title ?? game.name }}
                            </h3>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    class="rounded-lg px-2 py-1 text-xs text-white"
                                    :style="{ backgroundColor: statusColor(game) }"
                                >
                                    {{ normalizeStatus(game.status) }}
                                </span>

                                <span
                                    v-if="game.rating"
                                    class="rounded-lg bg-yellow-500/10 px-2 py-1 text-xs text-yellow-400"
                                >
                                    {{ ratingStars(game.rating) }}
                                </span>
                            </div>

                            <p
                                v-if="game.note"
                                class="mt-3 line-clamp-3 text-sm text-zinc-400"
                            >
                                {{ game.note }}
                            </p>

                            <p class="mt-4 text-xs text-zinc-600">
                                Last activity:
                                {{ game.updated_at ?? 'No updates' }}
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <div
            v-else
            class="rounded-2xl border border-dashed border-zinc-700 bg-zinc-950 p-8 text-center"
        >
            <p class="font-semibold text-white">
                No games found
            </p>

            <p class="mt-2 text-sm text-zinc-500">
                Try changing your search or filters.
            </p>
        </div>
    </div>
</template>