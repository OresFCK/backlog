<script setup>
import {
    onMounted,
    ref,
} from 'vue'

import {
    Search,
    Bell,
    X,
    Check,
} from 'lucide-vue-next'

import {
    Link,
    router,
} from '@inertiajs/vue3'

defineProps({
    user: {
        type: Object,
        default: null,
    },
})

const isOpen = ref(false)
const requests = ref([])
const count = ref(0)

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
        <div class="relative w-full max-w-md">
            <Search
                class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-500"
            />

            <input
                type="text"
                placeholder="Search games..."
                class="w-full rounded-xl border border-zinc-800 bg-zinc-900 py-3 pl-11 pr-4 text-sm text-white outline-none transition-all placeholder:text-zinc-500 focus:border-zinc-700"
            />
        </div>

        <div class="flex items-center gap-4">
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