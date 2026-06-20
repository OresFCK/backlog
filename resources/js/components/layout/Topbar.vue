<script setup>
import {
    computed,
    onMounted,
    ref,
    watch,
} from 'vue'

import {
    Bell,
    X,
    Check,
    Coins,
    PlusCircle,
    Search,
} from 'lucide-vue-next'

import {
    Link,
    router,
} from '@inertiajs/vue3'

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
})

const isOpen = ref(false)
const requests = ref([])
const adminNotifications = ref([])
const count = ref(0)

const searchQuery = ref('')
const searchResults = ref([])
const isSearching = ref(false)
const showSearchResults = ref(false)

let searchTimeout = null

const level = computed(() =>
    props.user?.level ?? 1
)

const coins = computed(() =>
    props.user?.coins ?? 0
)

const xp = computed(() =>
    props.user?.xp ?? 0
)

const nextLevelXp = computed(() =>
    props.user?.xp_for_next_level ?? 100
)

const currentLevelXp = computed(() =>
    props.user?.xp_for_current_level ?? 0
)

const xpProgress = computed(() => {
    const required =
        nextLevelXp.value - currentLevelXp.value

    if (required <= 0) {
        return 0
    }

    return Math.min(
        100,
        Math.max(
            0,
            ((xp.value - currentLevelXp.value) / required) * 100
        )
    )
})

const loadNotifications = async () => {
    const response = await fetch('/people/notifications')
    const data = await response.json()

    requests.value = data.incoming_requests ?? []
    adminNotifications.value = data.admin_notifications ?? []
    count.value = data.total_count ?? 0
}

const markNotificationsAsRead = async () => {
    await fetch(
        '/people/notifications/read',
        {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    ?.content,
            },
        }
    )

    count.value = requests.value.length
}

const toggleNotifications = async () => {
    isOpen.value = !isOpen.value

    if (
        isOpen.value &&
        adminNotifications.value.length
    ) {
        await markNotificationsAsRead()
    }
}

const acceptRequest = (request) => {
    router.patch(
        `/people/${request.id}/accept`,
        {},
        {
            preserveScroll: true,
            onSuccess: loadNotifications,
        }
    )
}

const declineRequest = (request) => {
    router.delete(
        `/people/${request.id}`,
        {
            preserveScroll: true,
            onSuccess: loadNotifications,
        }
    )
}

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

onMounted(() => {
    loadNotifications()
})
</script>

<template>
    <header
        class="flex h-[89px] items-center justify-between border-b border-zinc-800 bg-zinc-950 px-8"
    >
        <div class="relative w-full max-w-md">
            <Search class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-500" />

            <input
                v-model="searchQuery"
                type="text"
                placeholder="Search public games..."
                class="h-14 w-full rounded-2xl border border-zinc-800 bg-zinc-900 pl-12 pr-4 text-sm font-medium text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                @focus="showSearchResults = searchResults.length > 0"
            />

            <div
                v-if="showSearchResults"
                class="absolute left-0 top-16 z-50 w-full overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950 shadow-2xl"
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
                    />

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

        <div class="flex items-center gap-4">
            <Link
                href="/games/create"
                class="inline-flex h-14 items-center gap-2 rounded-2xl bg-white px-5 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
            >
                <PlusCircle class="h-5 w-5" />

                <span class="hidden md:block">
                    Add Game
                </span>
            </Link>

            <div
                class="hidden h-14 items-center gap-2 rounded-2xl border border-zinc-800 bg-zinc-900 px-4 text-sm font-bold text-white lg:flex"
            >
                <Coins class="h-4 w-4" />

                <span>{{ coins }}</span>
            </div>

            <div
                class="hidden h-14 w-56 rounded-2xl border border-zinc-800 bg-zinc-900 px-4 py-2 lg:block"
            >
                <div class="mb-2 flex items-center justify-between">
                    <p class="text-xs font-bold text-white">
                        Level {{ level }}
                    </p>

                    <p class="text-xs text-zinc-500">
                        {{ xp }} / {{ nextLevelXp }} XP
                    </p>
                </div>

                <div class="h-2 overflow-hidden rounded-full bg-zinc-800">
                    <div
                        class="h-full rounded-full bg-white transition-all"
                        :style="{
                            width: `${xpProgress}%`,
                        }"
                    />
                </div>
            </div>

            <div class="relative">
                <button
                    type="button"
                    class="relative flex h-14 w-14 items-center justify-center rounded-2xl border border-zinc-800 bg-zinc-900 text-zinc-300 transition hover:border-zinc-700 hover:text-white"
                    @click="toggleNotifications"
                >
                    <Bell class="h-5 w-5" />

                    <span
                        v-if="count"
                        class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold text-white"
                    >
                        {{ count }}
                    </span>
                </button>

                <!-- tutaj zostaw swoją całą sekcję notifications z poprzedniego pliku -->
            </div>

            <Link
                href="/profile"
                class="flex h-14 items-center gap-3 rounded-2xl border border-zinc-800 bg-zinc-900 px-3 transition hover:border-zinc-700"
            >
                <img
                    v-if="user?.avatar"
                    :src="user.avatar"
                    :alt="user.name"
                    class="h-10 w-10 rounded-full object-cover"
                />

                <div
                    v-else
                    class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500"
                />

                <div class="hidden text-left md:block">
                    <p class="text-sm font-semibold text-white">
                        {{ user?.name ?? 'Guest' }}
                    </p>

                    <p class="text-xs text-zinc-500">
                        Steam account
                    </p>
                </div>
            </Link>
        </div>
    </header>
</template>