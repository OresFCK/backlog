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
const results = ref([])
const loading = ref(false)

const duplicate = computed(() => {
    return props.games.some((game) => {
        const existingTitle = game.name ?? game.title ?? ''

        return (
            existingTitle.toLowerCase().trim() ===
            title.value.toLowerCase().trim()
        )
    })
})

let timeout = null

watch(title, (value) => {
    clearTimeout(timeout)

    if (!value || value.length < 2) {
        results.value = []
        return
    }

    timeout = setTimeout(async () => {
        loading.value = true

        const response = await fetch(
            `/steam/search?q=${encodeURIComponent(value)}`
        )

        results.value = await response.json()

        loading.value = false
    }, 350)
})

function selectGame(game) {
    title.value = game.title
    coverUrl.value = game.cover_url
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
                        Search Steam first to avoid adding duplicates.
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
                                placeholder="e.g. Hades"
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
                            v-if="results.length"
                            class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-5"
                        >
                            <h2 class="mb-4 text-lg font-bold text-white">
                                Steam matches
                            </h2>

                            <div class="space-y-3">
                                <button
                                    v-for="game in results"
                                    :key="game.appid"
                                    type="button"
                                    class="flex w-full items-center gap-4 rounded-2xl border border-zinc-800 bg-zinc-950 p-3 text-left transition hover:border-zinc-600"
                                    @click="selectGame(game)"
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
                            v-if="loading"
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
                                    placeholder="e.g. Valve"
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