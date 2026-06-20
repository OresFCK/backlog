<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    listId: {
        type: [String, Number],
        required: true,
    },

    games: {
        type: Array,
        default: () => [],
    },

    items: {
        type: Array,
        default: () => [],
    },
})

const searchQuery = ref('')
const selectedGame = ref(null)
const note = ref('')

const usedGameIds = computed(() =>
    props.items.map((item) => String(item.game_id))
)

const filteredGames = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()

    if (!query || selectedGame.value) {
        return []
    }

    return props.games
        .filter((game) => !usedGameIds.value.includes(String(game.id)))
        .filter((game) =>
            String(game.title ?? game.name ?? '')
                .toLowerCase()
                .includes(query)
        )
        .slice(0, 12)
})

const coverUrl = (game) => {
    if (!game) {
        return null
    }

    if (game.steam_app_id) {
        return `https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/${game.steam_app_id}/library_600x900.jpg`
    }

    return game.cover_url ?? game.header_image_url
}

const selectGame = (game) => {
    selectedGame.value = game
    searchQuery.value = game.title ?? game.name
}

const clearSelectedGame = () => {
    selectedGame.value = null
    searchQuery.value = ''
}

const addItem = () => {
    if (!selectedGame.value) {
        return
    }

    router.post(
        `/lists/${props.listId}/items`,
        {
            game_id: String(selectedGame.value.id),
            game_title: selectedGame.value.title ?? selectedGame.value.name,
            game_cover_url: coverUrl(selectedGame.value),
            game_slug: selectedGame.value.slug ?? null,
            steam_app_id: selectedGame.value.steam_app_id
                ? String(selectedGame.value.steam_app_id)
                : null,
            note: note.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedGame.value = null
                searchQuery.value = ''
                note.value = ''
            },
        }
    )
}
</script>

<template>
    <form
        class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6"
        @submit.prevent="addItem"
    >
        <h2 class="text-xl font-bold">
            Add game
        </h2>

        <p class="mt-1 text-sm text-zinc-500">
            {{ games.length }} games available
        </p>

        <div class="mt-5 space-y-4">
            <div class="relative">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search games..."
                    class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-500"
                    @input="selectedGame = null"
                />

                <div
                    v-if="filteredGames.length"
                    class="absolute left-0 right-0 top-full z-30 mt-2 max-h-96 overflow-y-auto rounded-2xl border border-zinc-700 bg-zinc-950 shadow-2xl"
                >
                    <button
                        v-for="game in filteredGames"
                        :key="game.id"
                        type="button"
                        class="flex w-full items-center gap-3 px-3 py-3 text-left transition hover:bg-zinc-900"
                        @click="selectGame(game)"
                    >
                        <img
                            v-if="coverUrl(game)"
                            :src="coverUrl(game)"
                            :alt="game.title"
                            class="h-16 w-12 shrink-0 rounded-lg object-cover"
                        />

                        <p class="truncate text-sm font-bold text-white">
                            {{ game.title ?? game.name }}
                        </p>
                    </button>
                </div>

                <p
                    v-if="searchQuery && !filteredGames.length && !selectedGame"
                    class="mt-2 text-sm text-zinc-500"
                >
                    No matching games found.
                </p>

                <div
                    v-if="selectedGame"
                    class="mt-3 flex items-center justify-between gap-3 rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2"
                >
                    <span
                        class="truncate text-sm font-bold text-white"
                    >
                        Selected:
                        {{ selectedGame.title ?? selectedGame.name }}
                    </span>

                    <button
                        type="button"
                        class="shrink-0 text-sm font-bold text-zinc-400 transition hover:text-white"
                        @click="clearSelectedGame"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <textarea
                v-model="note"
                rows="4"
                placeholder="Optional note for this entry..."
                class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-500"
            />

            <button
                type="submit"
                class="w-full rounded-xl bg-white px-4 py-3 text-sm font-black text-zinc-950 transition hover:bg-zinc-200 disabled:opacity-50"
                :disabled="!selectedGame"
            >
                Add to list
            </button>
        </div>
    </form>
</template>