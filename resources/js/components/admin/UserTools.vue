<script setup>
import { computed, onMounted, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const userSearch = ref('')
const foundUsers = ref([])
const selectedUser = ref(null)
const userLogs = ref([])

const shopItems = ref([])
const challenges = ref([])

const activeAction = ref('coins')

const actions = [
    { key: 'coins', label: 'Coins' },
    { key: 'xp', label: 'XP' },
    { key: 'level', label: 'Level' },
    { key: 'item', label: 'Item' },
    { key: 'challenge', label: 'Challenge' },
]

const coinsForm = useForm({
    amount: '',
    reason: '',
})

const xpForm = useForm({
    amount: '',
    reason: '',
})

const levelForm = useForm({
    level: '',
    reason: '',
})

const itemForm = useForm({
    shop_item_id: '',
    reason: '',
})

const challengeForm = useForm({
    challenge_id: '',
    grant_rewards: true,
    reason: '',
})

const selectedActionLabel = computed(() => {
    return actions.find((action) => action.key === activeAction.value)?.label
})

onMounted(async () => {
    const response = await fetch('/admin/grantables')

    if (response.ok) {
        const data = await response.json()

        shopItems.value = data.shopItems ?? []
        challenges.value = data.challenges ?? []
    }
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

const refreshSelectedUser = async () => {
    if (!selectedUser.value || !userSearch.value) {
        return
    }

    const response = await fetch(
        `/admin/users/search?q=${encodeURIComponent(userSearch.value)}`
    )

    const users = await response.json()
    const freshUser = users.find((user) => user.id === selectedUser.value.id)

    if (freshUser) {
        selectedUser.value = freshUser
    }
}

const afterSuccess = async (form) => {
    form.reset()

    await refreshSelectedUser()

    if (selectedUser.value) {
        await selectUser(selectedUser.value)
    }
}

const addCoins = () => {
    if (!selectedUser.value) return

    coinsForm.post(`/admin/users/${selectedUser.value.id}/coins`, {
        preserveScroll: true,
        onSuccess: () => afterSuccess(coinsForm),
    })
}

const addXp = () => {
    if (!selectedUser.value) return

    xpForm.post(`/admin/users/${selectedUser.value.id}/xp`, {
        preserveScroll: true,
        onSuccess: () => afterSuccess(xpForm),
    })
}

const setLevel = () => {
    if (!selectedUser.value) return

    levelForm.post(`/admin/users/${selectedUser.value.id}/level`, {
        preserveScroll: true,
        onSuccess: () => afterSuccess(levelForm),
    })
}

const grantItem = () => {
    if (!selectedUser.value) return

    itemForm.post(`/admin/users/${selectedUser.value.id}/items`, {
        preserveScroll: true,
        onSuccess: () => afterSuccess(itemForm),
    })
}

const completeChallenge = () => {
    if (!selectedUser.value) return

    challengeForm.post(`/admin/users/${selectedUser.value.id}/challenges`, {
        preserveScroll: true,
        onSuccess: () => afterSuccess(challengeForm),
    })
}
</script>

<template>
    <section class="grid gap-8 xl:grid-cols-[520px_1fr]">
        <div class="space-y-5">
            <div class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
                <h2 class="text-xl font-bold text-white">
                    User tools
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    Search user, select account and apply admin actions.
                </p>

                <div class="mt-6">
                    <label class="mb-2 block text-sm font-bold text-white">
                        Search user
                    </label>

                    <input
                        v-model="userSearch"
                        placeholder="Name, email or Steam ID"
                        class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        @input="searchUsers"
                    />
                </div>

                <div
                    v-if="foundUsers.length"
                    class="mt-4 space-y-2"
                >
                    <button
                        v-for="foundUser in foundUsers"
                        :key="foundUser.id"
                        type="button"
                        class="flex w-full items-center gap-3 rounded-2xl border p-3 text-left transition"
                        :class="
                            selectedUser?.id === foundUser.id
                                ? 'border-blue-500/50 bg-blue-500/10'
                                : 'border-zinc-800 bg-zinc-950 hover:border-zinc-700'
                        "
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

                        <span class="rounded-full bg-zinc-800 px-3 py-1 text-xs font-bold text-zinc-300">
                            Lv. {{ foundUser.level }}
                        </span>
                    </button>
                </div>
            </div>

            <div
                v-if="selectedUser"
                class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6"
            >
                <div class="flex items-center gap-4">
                    <img
                        v-if="selectedUser.avatar"
                        :src="selectedUser.avatar"
                        :alt="selectedUser.name"
                        class="h-14 w-14 rounded-full object-cover"
                    />

                    <div
                        v-else
                        class="h-14 w-14 rounded-full bg-zinc-800"
                    />

                    <div class="min-w-0">
                        <h3 class="truncate text-lg font-black text-white">
                            {{ selectedUser.name }}
                        </h3>

                        <p class="truncate text-xs text-zinc-500">
                            {{ selectedUser.email }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4">
                        <p class="text-xs text-zinc-500">
                            Level
                        </p>

                        <p class="mt-1 text-xl font-black text-white">
                            {{ selectedUser.level }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4">
                        <p class="text-xs text-zinc-500">
                            XP
                        </p>

                        <p class="mt-1 text-xl font-black text-white">
                            {{ selectedUser.xp }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4">
                        <p class="text-xs text-zinc-500">
                            Coins
                        </p>

                        <p class="mt-1 text-xl font-black text-white">
                            {{ selectedUser.coins }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-zinc-800 bg-zinc-950 p-2">
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
                        <button
                            v-for="action in actions"
                            :key="action.key"
                            type="button"
                            class="rounded-xl px-3 py-2 text-sm font-bold transition"
                            :class="
                                activeAction === action.key
                                    ? 'bg-white text-zinc-950'
                                    : 'text-zinc-400 hover:bg-zinc-800 hover:text-white'
                            "
                            @click="activeAction = action.key"
                        >
                            {{ action.label }}
                        </button>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
                    <h3 class="mb-4 text-lg font-black text-white">
                        {{ selectedActionLabel }}
                    </h3>

                    <form
                        v-if="activeAction === 'coins'"
                        class="space-y-3"
                        @submit.prevent="addCoins"
                    >
                        <input
                            v-model="coinsForm.amount"
                            type="number"
                            min="1"
                            placeholder="Coins amount, e.g. 500"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        />

                        <input
                            v-model="coinsForm.reason"
                            placeholder="Reason"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        />

                        <button
                            type="submit"
                            class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200 disabled:opacity-50"
                            :disabled="coinsForm.processing"
                        >
                            Add coins
                        </button>
                    </form>

                    <form
                        v-if="activeAction === 'xp'"
                        class="space-y-3"
                        @submit.prevent="addXp"
                    >
                        <input
                            v-model="xpForm.amount"
                            type="number"
                            min="1"
                            placeholder="XP amount, e.g. 1000"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        />

                        <input
                            v-model="xpForm.reason"
                            placeholder="Reason"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        />

                        <button
                            type="submit"
                            class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200 disabled:opacity-50"
                            :disabled="xpForm.processing"
                        >
                            Add XP
                        </button>
                    </form>

                    <form
                        v-if="activeAction === 'level'"
                        class="space-y-3"
                        @submit.prevent="setLevel"
                    >
                        <input
                            v-model="levelForm.level"
                            type="number"
                            min="1"
                            placeholder="Set level, e.g. 10"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        />

                        <input
                            v-model="levelForm.reason"
                            placeholder="Reason"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        />

                        <button
                            type="submit"
                            class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200 disabled:opacity-50"
                            :disabled="levelForm.processing"
                        >
                            Set level
                        </button>
                    </form>

                    <form
                        v-if="activeAction === 'item'"
                        class="space-y-3"
                        @submit.prevent="grantItem"
                    >
                        <select
                            v-model="itemForm.shop_item_id"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        >
                            <option value="">
                                Select item
                            </option>

                            <option
                                v-for="item in shopItems"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }} · {{ item.type }}
                            </option>
                        </select>

                        <input
                            v-model="itemForm.reason"
                            placeholder="Reason"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        />

                        <button
                            type="submit"
                            class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200 disabled:opacity-50"
                            :disabled="itemForm.processing || !itemForm.shop_item_id"
                        >
                            Grant item
                        </button>
                    </form>

                    <form
                        v-if="activeAction === 'challenge'"
                        class="space-y-3"
                        @submit.prevent="completeChallenge"
                    >
                        <select
                            v-model="challengeForm.challenge_id"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        >
                            <option value="">
                                Select challenge
                            </option>

                            <option
                                v-for="challenge in challenges"
                                :key="challenge.id"
                                :value="challenge.id"
                            >
                                {{ challenge.title }} · {{ challenge.game_name }}
                            </option>
                        </select>

                        <label class="flex items-center gap-3 text-sm text-zinc-300">
                            <input
                                v-model="challengeForm.grant_rewards"
                                type="checkbox"
                                class="h-4 w-4 rounded border-zinc-700 bg-zinc-950"
                            />

                            Grant challenge rewards
                        </label>

                        <input
                            v-model="challengeForm.reason"
                            placeholder="Reason"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        />

                        <button
                            type="submit"
                            class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200 disabled:opacity-50"
                            :disabled="challengeForm.processing || !challengeForm.challenge_id"
                        >
                            Complete challenge
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900">
    <div class="border-b border-zinc-800 p-6">
        <h2 class="text-xl font-bold text-white">
            User activity
        </h2>

        <p class="mt-1 text-sm text-zinc-500">
            Shows actions performed by the selected user.
        </p>
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
                {{ log.created_at }}
            </p>
        </div>

        <div
            v-if="!userLogs.length"
            class="p-10 text-center text-zinc-500"
        >
            Search user to see their activity.
        </div>
    </div>
</div>
    </section>
</template>