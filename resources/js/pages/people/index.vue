<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'

import {
    UserPlus,
    Eye,
    Trash2,
    Search,
    Check,
    X,
    Copy,
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

    incomingRequests: {
        type: Array,
        default: () => [],
    },
})

const query = ref('')
const results = ref([])
const loading = ref(false)
const searched = ref(false)

let searchTimeout = null

const friends = computed(() =>
    props.connections.filter(
        (connection) =>
            connection.type === 'friend' &&
            connection.status === 'accepted'
    )
)

const followed = computed(() =>
    props.connections.filter(
        (connection) =>
            connection.type === 'follow' &&
            connection.status === 'accepted'
    )
)

const search = async () => {
    clearTimeout(searchTimeout)

    searchTimeout = setTimeout(async () => {
        if (! query.value.trim()) {
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

const addConnection = (userId, type) => {
    router.post(
        '/people',
        {
            user_id: userId,
            type,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                search()
            },
        }
    )
}

const inviteUser = async (steamId) => {
    await navigator.clipboard.writeText(
        `${window.location.origin}/invite/${steamId}`
    )

    alert('Invite link copied to clipboard.')
}

const acceptRequest = (request) => {
    router.patch(
        `/people/${request.id}/accept`,
        {},
        {
            preserveScroll: true,
        }
    )
}

const removeConnection = (connection) => {
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
                        Find users by Steam profile URL, vanity name or SteamID64.
                    </p>
                </div>

                <section
                    v-if="incomingRequests.length"
                    class="mb-8 rounded-3xl border border-zinc-800 bg-zinc-900 p-6"
                >
                    <h2 class="mb-4 text-2xl font-black text-white">
                        Friend requests
                    </h2>

                    <div class="space-y-3">
                        <article
                            v-for="request in incomingRequests"
                            :key="request.id"
                            class="flex items-center justify-between rounded-2xl border border-zinc-800 bg-zinc-950 p-4"
                        >
                            <div class="flex items-center gap-4">
                                <img
                                    v-if="request.user.avatar"
                                    :src="request.user.avatar"
                                    class="h-12 w-12 rounded-2xl object-cover"
                                />

                                <div>
                                    <p class="font-bold text-white">
                                        {{ request.user.name }}
                                    </p>

                                    <p class="text-sm text-zinc-500">
                                        {{ request.user.steam_id }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                                    @click="acceptRequest(request)"
                                >
                                    <Check class="h-4 w-4" />

                                    Accept
                                </button>

                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-zinc-800"
                                    @click="removeConnection(request)"
                                >
                                    <X class="h-4 w-4" />

                                    Decline
                                </button>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
                    <div class="relative">
                        <Search class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-500" />

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
                        Searching users...
                    </div>

                    <div
                        v-if="! loading && searched && ! results.length"
                        class="mt-5 rounded-2xl border border-dashed border-zinc-800 p-6 text-sm text-zinc-500"
                    >
                        No Steam users found.
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
                                </div>
                            </div>

                            <div
                                v-if="result.exists"
                                class="flex items-center gap-3"
                            >
                                <button
                                    v-if="! result.is_friend && ! result.friend_request_pending"
                                    type="button"
                                    class="flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                                    @click="addConnection(result.id, 'friend')"
                                >
                                    <UserPlus class="h-4 w-4" />

                                    Add friend
                                </button>

                                <span
                                    v-else-if="result.is_friend"
                                    class="rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-zinc-400"
                                >
                                    Already friends
                                </span>

                                <span
                                    v-else-if="result.friend_request_pending"
                                    class="rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-zinc-400"
                                >
                                    Request sent
                                </span>

                                <span
                                    v-else
                                    class="rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-zinc-400"
                                >
                                    Following
                                </span>
                            </div>

                            <button
                                v-else
                                type="button"
                                class="flex items-center gap-2 rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-zinc-800"
                                @click="inviteUser(result.steam_id)"
                            >
                                <Copy class="h-4 w-4" />

                                Invite
                            </button>
                        </article>
                    </div>
                </section>

                <div class="mt-10 grid gap-8">
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
                                    <img
                                        v-if="connection.user.avatar"
                                        :src="connection.user.avatar"
                                        class="h-12 w-12 rounded-2xl object-cover"
                                    />

                                    <div>
                                        <p class="font-bold text-white">
                                            {{ connection.user.name }}
                                        </p>

                                        <p class="text-sm text-zinc-500">
                                            {{ connection.user.steam_id }}
                                        </p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="rounded-xl p-2 text-zinc-500 transition hover:bg-zinc-800 hover:text-red-300"
                                    @click="removeConnection(connection)"
                                >
                                    <Trash2 class="h-5 w-5" />
                                </button>
                            </article>

                            <div
                                v-if="! friends.length"
                                class="rounded-2xl border border-dashed border-zinc-800 p-10 text-center text-zinc-500"
                            >
                                No friends yet.
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>
</template>