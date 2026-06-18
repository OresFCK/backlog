<script setup>
import { computed, ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

const props = defineProps({
    user: Object,

    list: {
        type: Object,
        required: true,
    },

    games: {
        type: Array,
        default: () => [],
    },
})

const searchQuery = ref('')
const selectedGame = ref(null)
const note = ref('')
const draggedIndex = ref(null)

const localItems = ref([])

const syncItems = () => {
    localItems.value = [...(props.list.items ?? [])].sort(
        (a, b) => a.position - b.position
    )
}

syncItems()

watch(
    () => props.list.items,
    () => syncItems(),
    { deep: true }
)

const usedGameIds = computed(() =>
    localItems.value.map((item) => String(item.game_id))
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
        `/lists/${props.list.id}/items`,
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

const removeItem = (item) => {
    router.delete(`/lists/${props.list.id}/items/${item.id}`, {
        preserveScroll: true,
    })
}

const persistOrder = () => {
    router.patch(
        `/lists/${props.list.id}/items/reorder`,
        {
            items: localItems.value.map((item, index) => ({
                id: item.id,
                position: index + 1,
            })),
        },
        {
            preserveScroll: true,
        }
    )
}

const reorderItems = (fromIndex, toIndex) => {
    if (
        fromIndex === null ||
        fromIndex === toIndex ||
        toIndex < 0 ||
        toIndex >= localItems.value.length
    ) {
        return
    }

    const items = [...localItems.value]
    const [movedItem] = items.splice(fromIndex, 1)

    items.splice(toIndex, 0, movedItem)

    localItems.value = items.map((item, index) => ({
        ...item,
        position: index + 1,
    }))

    persistOrder()
}

const moveItem = (index, direction) => {
    reorderItems(index, index + direction)
}

const onDragStart = (index) => {
    draggedIndex.value = index
}

const onDrop = (index) => {
    reorderItems(draggedIndex.value, index)
    draggedIndex.value = null
}

const onDragEnd = () => {
    draggedIndex.value = null
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950 text-white">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 space-y-8 p-8">
                <section>
                    <Link
                        href="/lists"
                        class="text-sm font-bold text-zinc-500 transition hover:text-white"
                    >
                        ← Back to lists
                    </Link>

                    <h1 class="mt-4 text-3xl font-black">
                        {{ list.title }}
                    </h1>

                    <p class="mt-2 max-w-2xl text-zinc-400">
                        {{ list.description || 'No description yet.' }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="rounded-full border border-zinc-700 px-3 py-1 text-xs text-zinc-400">
                            {{ list.visibility }}
                        </span>

                        <span class="rounded-full border border-zinc-700 px-3 py-1 text-xs text-zinc-400">
                            {{ localItems.length }} games
                        </span>
                    </div>
                </section>

                <section class="grid gap-6 xl:grid-cols-[420px_1fr]">
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
                                    <span class="truncate text-sm font-bold text-white">
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

                    <div class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-xl font-bold">
                                Ranking
                            </h2>

                            <p class="text-sm text-zinc-500">
                                Drag items to reorder.
                            </p>
                        </div>

                        <div
                            v-if="localItems.length"
                            class="space-y-4"
                        >
                            <div
                                v-for="(item, index) in localItems"
                                :key="item.id"
                                draggable="true"
                                class="flex cursor-grab gap-4 rounded-2xl border border-zinc-800 bg-zinc-950 p-4 transition active:cursor-grabbing"
                                :class="{
                                    'opacity-40 ring-2 ring-zinc-600':
                                        draggedIndex === index,
                                }"
                                @dragstart="onDragStart(index)"
                                @dragover.prevent
                                @drop="onDrop(index)"
                                @dragend="onDragEnd"
                            >
                                <div class="flex w-10 shrink-0 flex-col items-center justify-center">
                                    <span class="text-2xl font-black text-zinc-600">
                                        #{{ index + 1 }}
                                    </span>

                                    <span class="mt-1 text-xs text-zinc-600">
                                        ⋮⋮
                                    </span>
                                </div>

                                <img
                                    v-if="item.game_cover_url"
                                    :src="item.game_cover_url"
                                    :alt="item.game_title"
                                    class="h-28 w-20 shrink-0 rounded-xl object-cover"
                                />

                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate font-black text-white">
                                        {{ item.game_title }}
                                    </h3>

                                    <p
                                        v-if="item.note"
                                        class="mt-2 line-clamp-3 text-sm text-zinc-400"
                                    >
                                        {{ item.note }}
                                    </p>

                                    <p
                                        v-else
                                        class="mt-2 text-sm text-zinc-600"
                                    >
                                        No note.
                                    </p>
                                </div>

                                <div class="flex shrink-0 flex-col gap-2">
                                    <button
                                        type="button"
                                        class="rounded-lg border border-zinc-700 px-3 py-2 text-sm font-bold text-white transition hover:bg-zinc-800 disabled:opacity-40"
                                        :disabled="index === 0"
                                        @click.stop="moveItem(index, -1)"
                                    >
                                        ↑
                                    </button>

                                    <button
                                        type="button"
                                        class="rounded-lg border border-zinc-700 px-3 py-2 text-sm font-bold text-white transition hover:bg-zinc-800 disabled:opacity-40"
                                        :disabled="index === localItems.length - 1"
                                        @click.stop="moveItem(index, 1)"
                                    >
                                        ↓
                                    </button>

                                    <button
                                        type="button"
                                        class="rounded-lg border border-red-900/60 px-3 py-2 text-sm font-bold text-red-400 transition hover:bg-red-950/40"
                                        @click.stop="removeItem(item)"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="rounded-2xl border border-dashed border-zinc-700 bg-zinc-950 p-10 text-center"
                        >
                            <p class="font-bold">
                                Empty list
                            </p>

                            <p class="mt-2 text-sm text-zinc-500">
                                Add games to start building your ranking.
                            </p>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>