<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const userSearch = ref('')
const foundUsers = ref([])
const selectedUser = ref(null)
const userLogs = ref([])

const coinsForm = useForm({
    amount: 0,
    reason: '',
})

const searchUsers = async () => {
    if (!userSearch.value) {
        foundUsers.value = []
        return
    }

    const response = await fetch(
        `/admin/users/search?q=${encodeURIComponent(userSearch.value)}`
    )

    foundUsers.value = await response.json()
}

const selectUser = async (user) => {
    selectedUser.value = user

    const response = await fetch(`/admin/users/${user.id}/logs`)
    userLogs.value = await response.json()
}

const addCoins = () => {
    if (!selectedUser.value) {
        return
    }

    coinsForm.post(`/admin/users/${selectedUser.value.id}/coins`, {
        preserveScroll: true,
        onSuccess: () => {
            coinsForm.reset()
            selectUser(selectedUser.value)
        },
    })
}
</script>

<template>
    <section class="grid gap-8 xl:grid-cols-[420px_1fr]">
        <div class="space-y-5 rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
            <h2 class="text-xl font-bold text-white">
                User tools
            </h2>

            <div
                class="rounded-2xl border border-blue-500/20 bg-blue-500/5 p-4"
            >
                <h3 class="mb-3 text-sm font-bold text-blue-300">
                    User management
                </h3>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-zinc-400">
                            Search
                        </span>

                        <span class="text-right font-medium text-white">
                            Find users by name, email or Steam ID
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-zinc-400">
                            Coins
                        </span>

                        <span class="text-right font-medium text-white">
                            Add wallet currency manually
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-zinc-400">
                            Logs
                        </span>

                        <span class="text-right font-medium text-white">
                            Check recent account activity
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-white">
                    Search user
                </label>

                <p class="mb-3 text-xs text-zinc-500">
                    Search by username, email address or Steam ID.
                </p>

                <input
                    v-model="userSearch"
                    placeholder="Example: user@email.com"
                    class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                    @input="searchUsers"
                />
            </div>

            <div
                v-if="foundUsers.length"
                class="space-y-2"
            >
                <button
                    v-for="foundUser in foundUsers"
                    :key="foundUser.id"
                    type="button"
                    class="flex w-full items-center gap-3 rounded-2xl border border-zinc-800 bg-zinc-950 p-3 text-left transition hover:border-zinc-700"
                    @click="selectUser(foundUser)"
                >
                    <img
                        v-if="foundUser.avatar"
                        :src="foundUser.avatar"
                        :alt="foundUser.name"
                        class="h-10 w-10 rounded-full object-cover"
                    />

                    <div
                        v-else
                        class="h-10 w-10 rounded-full bg-zinc-800"
                    />

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-white">
                            {{ foundUser.name }}
                        </p>

                        <p class="truncate text-xs text-zinc-500">
                            {{ foundUser.email }}
                        </p>
                    </div>

                    <span
                        class="rounded-full bg-zinc-800 px-3 py-1 text-xs font-bold text-zinc-300"
                    >
                        Lv. {{ foundUser.level }}
                    </span>
                </button>
            </div>

            <div
                v-if="selectedUser"
                class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4"
            >
                <div class="flex items-center gap-3">
                    <img
                        v-if="selectedUser.avatar"
                        :src="selectedUser.avatar"
                        :alt="selectedUser.name"
                        class="h-12 w-12 rounded-full object-cover"
                    />

                    <div
                        v-else
                        class="h-12 w-12 rounded-full bg-zinc-800"
                    />

                    <div class="min-w-0">
                        <h3 class="truncate font-bold text-white">
                            {{ selectedUser.name }}
                        </h3>

                        <p class="truncate text-xs text-zinc-500">
                            {{ selectedUser.email }}
                        </p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4">
                        <p class="text-xs text-zinc-500">
                            Level
                        </p>

                        <p class="mt-1 text-lg font-bold text-white">
                            {{ selectedUser.level }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4">
                        <p class="text-xs text-zinc-500">
                            XP
                        </p>

                        <p class="mt-1 text-lg font-bold text-white">
                            {{ selectedUser.xp }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4">
                        <p class="text-xs text-zinc-500">
                            Coins
                        </p>

                        <p class="mt-1 text-lg font-bold text-white">
                            {{ selectedUser.coins }}
                        </p>
                    </div>
                </div>

                <form
                    class="mt-5 space-y-3"
                    @submit.prevent="addCoins"
                >
                    <div>
                        <label class="mb-2 block text-sm font-bold text-white">
                            Coins amount
                        </label>

                        <p class="mb-3 text-xs text-zinc-500">
                            Amount of currency to add to this user.
                        </p>

                        <input
                            v-model="coinsForm.amount"
                            type="number"
                            min="1"
                            placeholder="Example: 500"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-white">
                            Reason
                        </label>

                        <p class="mb-3 text-xs text-zinc-500">
                            Optional note saved in the activity log.
                        </p>

                        <input
                            v-model="coinsForm.reason"
                            placeholder="Example: Manual reward"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        />
                    </div>

                    <button
                        type="submit"
                        class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                        :disabled="coinsForm.processing"
                    >
                        Add coins
                    </button>
                </form>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900"
        >
            <div class="border-b border-zinc-800 p-6">
                <h2 class="text-xl font-bold text-white">
                    Activity logs
                </h2>
            </div>

            <div class="divide-y divide-zinc-800">
                <div
                    v-for="log in userLogs"
                    :key="log.id"
                    class="p-5"
                >
                    <p class="font-bold text-white">
                        {{ log.message }}
                    </p>

                    <p class="mt-1 text-sm text-zinc-500">
                        {{ log.type }} · {{ log.created_at }}
                    </p>
                </div>

                <div
                    v-if="!userLogs.length"
                    class="p-10 text-center text-zinc-500"
                >
                    Search user to see activity logs.
                </div>
            </div>
        </div>
    </section>
</template>