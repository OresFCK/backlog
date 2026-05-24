<script setup>
import {
    computed,
    ref,
} from 'vue'

import {
    router,
} from '@inertiajs/vue3'

import {
    UserPlus,
    Eye,
    Trash2,
    Search,
    ExternalLink,
} from 'lucide-vue-next'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },

    connections: {
        type: Array,
        default: () => [],
    },
})

const query = ref('')
const results = ref([])
const loading = ref(false)
const searched = ref(false)

const friends = computed(() =>
    props.connections.filter(
        (connection) =>
            connection.type ===
            'friend'
    )
)

const followed = computed(() =>
    props.connections.filter(
        (connection) =>
            connection.type ===
            'follow'
    )
)

let searchTimeout = null

const search = async () => {
    clearTimeout(searchTimeout)

    searchTimeout = setTimeout(async () => {
        if (!query.value.trim()) {
            results.value = []
            searched.value = false

            return
        }

        loading.value = true
        searched.value = true

        try {
            const response = await fetch(
                `/people/search?q=${encodeURIComponent(query.value)}`
            )

            results.value = await response.json()
        } finally {
            loading.value = false
        }
    }, 300)
}

const addConnection = (
    steamId,
    type
) => {
    router.post(
        '/people',
        {
            steam_id: steamId,
            type,
        },
        {
            preserveScroll: true,
        }
    )
}

const removeConnection = (
    connection
) => {
    router.delete(
        `/people/${connection.id}`,
        {
            preserveScroll: true,
        }
    )
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 p-8">
                <div class="mb-8">
                    <h1 class="text-4xl font-black text-white">
                        People
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Find people by Steam profile URL, vanity name or SteamID64.
                    </p>
                </div>

                <section class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
                    <div class="relative">
                        <Search
                            class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-500"
                        />

                        <input
                            v-model="query"
                            type="text"
                            placeholder="Steam profile URL, vanity name or SteamID64"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 py-4 pl-12 pr-4 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-500"
                            @input="search"
                        />
                    </div>

                    <div
                        v-if="loading"
                        class="mt-5 text-sm text-zinc-500"
                    >
                        Searching Steam profile...
                    </div>

                    <div
                        v-if="!loading && searched && !results.length"
                        class="mt-5 rounded-2xl border border-dashed border-zinc-800 p-6 text-sm text-zinc-500"
                    >
                        No Steam profile found. Try SteamID64 or profile URL.
                    </div>

                    <div
                        v-if="results.length"
                        class="mt-5 space-y-3"
                    >
                        <article
                            v-for="result in results"
                            :key="result.steam_id"
                            class="flex items-center justify-between gap-4 rounded-2xl border border-zinc-800 bg-zinc-950 p-4"
                        >
                            <div class="flex items-center gap-4">
                                <img
                                    v-if="result.avatar"
                                    :src="result.avatar"
                                    class="h-14 w-14 rounded-2xl object-cover"
                                />

                                <div>
                                    <p class="font-bold text-white">
                                        {{ result.name }}
                                    </p>

                                    <p class="mt-1 text-sm text-zinc-500">
                                        {{ result.steam_id }}
                                    </p>

                                    <a
                                        v-if="result.profile_url"
                                        :href="result.profile_url"
                                        target="_blank"
                                        rel="noreferrer"
                                        class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-zinc-400 transition hover:text-white"
                                    >
                                        View Steam profile
                                        <ExternalLink class="h-3 w-3" />
                                    </a>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                                    @click="
                                        addConnection(
                                            result.steam_id,
                                            'friend'
                                        )
                                    "
                                >
                                    <UserPlus class="h-4 w-4" />
                                    Add friend
                                </button>

                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-zinc-800"
                                    @click="
                                        addConnection(
                                            result.steam_id,
                                            'follow'
                                        )
                                    "
                                >
                                    <Eye class="h-4 w-4" />
                                    Follow
                                </button>
                            </div>
                        </article>
                    </div>
                </section>

                <div class="mt-10 grid gap-8 lg:grid-cols-2">
                    <section>
                        <h2 class="mb-4 text-2xl font-black text-white">
                            Friends
                        </h2>

                        <div class="space-y-4">
                            <article
                                v-for="connection in friends"
                                :key="connection.id"
                                class="flex items-center justify-between gap-4 rounded-2xl border border-zinc-800 bg-zinc-900 p-5"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-800 text-zinc-300">
                                        <UserPlus class="h-5 w-5" />
                                    </div>

                                    <div>
                                        <p class="font-bold text-white">
                                            {{ connection.steam_id }}
                                        </p>

                                        <p class="text-sm capitalize text-zinc-500">
                                            {{ connection.status }}
                                        </p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="rounded-xl p-2 text-zinc-500 transition hover:bg-zinc-800 hover:text-red-300"
                                    @click="
                                        removeConnection(
                                            connection
                                        )
                                    "
                                >
                                    <Trash2 class="h-5 w-5" />
                                </button>
                            </article>

                            <div
                                v-if="!friends.length"
                                class="rounded-2xl border border-dashed border-zinc-800 p-10 text-center text-zinc-500"
                            >
                                No friends yet.
                            </div>
                        </div>
                    </section>

                    <section>
                        <h2 class="mb-4 text-2xl font-black text-white">
                            Followed
                        </h2>

                        <div class="space-y-4">
                            <article
                                v-for="connection in followed"
                                :key="connection.id"
                                class="flex items-center justify-between gap-4 rounded-2xl border border-zinc-800 bg-zinc-900 p-5"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-800 text-zinc-300">
                                        <Eye class="h-5 w-5" />
                                    </div>

                                    <div>
                                        <p class="font-bold text-white">
                                            {{ connection.steam_id }}
                                        </p>

                                        <p class="text-sm capitalize text-zinc-500">
                                            {{ connection.status }}
                                        </p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="rounded-xl p-2 text-zinc-500 transition hover:bg-zinc-800 hover:text-red-300"
                                    @click="
                                        removeConnection(
                                            connection
                                        )
                                    "
                                >
                                    <Trash2 class="h-5 w-5" />
                                </button>
                            </article>

                            <div
                                v-if="!followed.length"
                                class="rounded-2xl border border-dashed border-zinc-800 p-10 text-center text-zinc-500"
                            >
                                You are not following anyone yet.
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>
</template>