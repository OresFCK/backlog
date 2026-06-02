<script setup>
import {
    computed,
    onMounted,
    ref,
} from 'vue'

import {
    Search,
    Bell,
    X,
    Check,
    Coins,
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
const count = ref(0)

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
    count.value = data.incoming_requests_count ?? 0
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

onMounted(() => {
    loadNotifications()
})
</script>

<template>
    <header
        class="flex h-[89px] items-center justify-between border-b border-zinc-800 bg-zinc-950 px-8"
    >
        <div class="relative w-full max-w-md"></div>

        <div class="flex items-center gap-4">
            <div
                class="hidden items-center gap-2 rounded-2xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-sm font-bold text-white lg:flex"
            >
                <Coins class="h-4 w-4 text-yellow-400" />

                <span>{{ coins }}</span>
            </div>
            
            <div
                class="hidden w-56 rounded-2xl border border-zinc-800 bg-zinc-900 px-4 py-3 lg:block"
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
                    class="relative flex h-12 w-12 items-center justify-center rounded-2xl border border-zinc-800 bg-zinc-900 text-zinc-300 transition hover:border-zinc-700 hover:text-white"
                    @click="isOpen = !isOpen"
                >
                    <Bell class="h-5 w-5" />

                    <span
                        v-if="count"
                        class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold text-white"
                    >
                        {{ count }}
                    </span>
                </button>

                <div
                    v-if="isOpen"
                    class="absolute right-0 top-14 z-50 w-96 rounded-2xl border border-zinc-800 bg-zinc-950 p-4 shadow-2xl"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="font-bold text-white">
                            Notifications
                        </h2>

                        <button
                            type="button"
                            class="rounded-lg p-1 text-zinc-500 hover:bg-zinc-900 hover:text-white"
                            @click="isOpen = false"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div
                        v-if="requests.length"
                        class="space-y-3"
                    >
                        <article
                            v-for="request in requests"
                            :key="request.id"
                            class="rounded-xl border border-zinc-800 bg-zinc-900 p-4"
                        >
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="request.user.avatar"
                                    :src="request.user.avatar"
                                    class="h-10 w-10 rounded-full object-cover"
                                />

                                <div>
                                    <p class="text-sm font-semibold text-white">
                                        {{ request.user.name }}
                                    </p>

                                    <p class="text-xs text-zinc-500">
                                        Sent you a friend request
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 flex gap-2">
                                <button
                                    type="button"
                                    class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-bold text-zinc-950 transition hover:bg-zinc-200"
                                    @click="acceptRequest(request)"
                                >
                                    <Check class="h-4 w-4" />

                                    Accept
                                </button>

                                <button
                                    type="button"
                                    class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-zinc-700 px-3 py-2 text-xs font-bold text-zinc-300 transition hover:bg-zinc-800 hover:text-white"
                                    @click="declineRequest(request)"
                                >
                                    <X class="h-4 w-4" />

                                    Decline
                                </button>
                            </div>
                        </article>
                    </div>

                    <div
                        v-else
                        class="rounded-xl border border-dashed border-zinc-800 p-6 text-center text-sm text-zinc-500"
                    >
                        No notifications.
                    </div>
                </div>
            </div>

            <Link
                href="/profile"
                class="flex items-center gap-3 rounded-2xl border border-zinc-800 bg-zinc-900 px-3 py-2 transition hover:border-zinc-700"
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