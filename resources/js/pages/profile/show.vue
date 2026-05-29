<script setup>
import { computed, ref } from 'vue'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

const props = defineProps({
    user: Object,
    games: Array,
    activity: Array,

    equippedItems: {
        type: Array,
        default: () => [],
    },
})

const openActivity = ref(false)
const openStatuses = ref({})

const equippedByType = computed(() => {
    return props.equippedItems.reduce((items, item) => {
        items[item.type] = item

        return items
    }, {})
})

const profileTheme = computed(() => {
    return equippedByType.value.theme
})

const usernameFontStyle = computed(() => {
    const font = equippedByType.value.username_font?.metadata?.font_family

    return font
        ? { fontFamily: font }
        : {}
})

const profileBackgroundStyle = computed(() => {
    if (!profileTheme.value?.image_url) {
        return {}
    }

    return {
        backgroundImage: `url(${profileTheme.value.image_url})`,
        backgroundSize: 'cover',
        backgroundAttachment: 'fixed',
        backgroundPosition: 'center',
    }
})

const ratingStars = (rating) => {
    if (!rating) {
        return '—'
    }

    return '★'.repeat(rating)
}

const normalizeStatus = (status) => {
    if (!status) {
        return 'Backlog'
    }

    const normalized = status.toLowerCase()

    const mappedStatuses = {
        backlog: 'Backlog',
        playing: 'Playing',
        finished: 'Finished',
        completed: 'Completed',
        wishlist: 'Wishlist',
        dropped: 'Dropped',
    }

    return mappedStatuses[normalized] ?? status
}

const groupedGames = computed(() => {
    return props.games.reduce((groups, game) => {
        const status = normalizeStatus(game.status)

        if (!groups[status]) {
            groups[status] = []
        }

        groups[status].push(game)

        return groups
    }, {})
})

const stats = computed(() => {
    const finishedStatuses = [
        'Finished',
        'Completed',
    ]

    const finished = props.games.filter(
        game => finishedStatuses.includes(
            normalizeStatus(game.status)
        )
    ).length

    return {
        played: props.games.filter(
            game => game.playtime_forever > 0
        ).length,

        playing: props.games.filter(
            game => normalizeStatus(game.status) === 'Playing'
        ).length,

        reviews: props.games.filter(
            game => game.rating || game.note
        ).length,

        backlog: props.games.filter(
            game => normalizeStatus(game.status) === 'Backlog'
        ).length,

        wishlist: props.games.filter(
            game => normalizeStatus(game.status) === 'Wishlist'
        ).length,

        lists: Object.keys(groupedGames.value).length,

        finished,

        completionRate: props.games.length
            ? Math.round((finished / props.games.length) * 1000) / 10
            : 0,
    }
})

const statusColor = (item) => {
    return item.status_color ?? '#71717a'
}

const toggleStatus = (status) => {
    openStatuses.value[status] =
        !openStatuses.value[status]
}
</script>

<template>
    <div
        class="flex min-h-screen bg-zinc-950 text-white"
        :style="profileBackgroundStyle"
    >
        <Sidebar />

        <div class="flex min-h-screen flex-1 flex-col bg-zinc-950/80 backdrop-blur-sm">
            <Topbar :user="user" />

            <main class="flex-1 px-8 py-10">
                <div class="mx-auto max-w-7xl space-y-8">
                    <div
                        class="relative overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900"
                    >
                        <img
                            v-if="equippedByType.profile_banner?.image_url"
                            :src="equippedByType.profile_banner.image_url"
                            :alt="equippedByType.profile_banner.name"
                            class="absolute inset-0 h-full w-full object-cover"
                        />

                        <div
                            v-else
                            class="absolute inset-0 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900"
                        />

                        <div class="pointer-events-none absolute inset-0 bg-black/45" />

                        <div
                            class="relative z-10 flex flex-col gap-6 p-8 pt-44 lg:flex-row lg:items-end"
                        >
                            <div class="relative h-32 w-32 shrink-0">
                                <img
                                    v-if="user?.avatar"
                                    :src="user.avatar"
                                    :alt="user.name"
                                    class="h-32 w-32 rounded-3xl border-4 border-zinc-900 object-cover shadow-2xl"
                                />

                                <div
                                    v-else
                                    class="h-32 w-32 rounded-3xl border-4 border-zinc-900 bg-gradient-to-br from-indigo-500 to-purple-500"
                                />

                                <img
                                    v-if="equippedByType.profile_overlay?.image_url"
                                    :src="equippedByType.profile_overlay.image_url"
                                    :alt="equippedByType.profile_overlay.name"
                                    class="pointer-events-none absolute inset-0 h-full w-full rounded-3xl object-cover"
                                />
                            </div>

                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h1
                                        class="text-5xl font-bold tracking-tight"
                                        :style="usernameFontStyle"
                                    >
                                        {{ user?.name }}
                                    </h1>

                                    <img
                                        v-if="equippedByType.badge?.image_url"
                                        :src="equippedByType.badge.image_url"
                                        :alt="equippedByType.badge.name"
                                        class="h-9 w-9 rounded-lg object-cover"
                                    />
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        v-if="equippedByType.user_title"
                                        class="rounded-full border border-zinc-700 bg-zinc-900/80 px-3 py-1 text-sm font-medium text-white"
                                    >
                                        {{ equippedByType.user_title.name }}
                                    </span>
                                </div>

                                <p class="mt-3 text-zinc-300">
                                    Steam ID:
                                    {{ user?.steam_id }}
                                </p>

                                <div class="mt-6 flex flex-wrap gap-3">
                                    <a
                                        :href="`https://steamcommunity.com/profiles/${user?.steam_id}`"
                                        target="_blank"
                                        class="inline-flex rounded-xl border border-zinc-700 bg-zinc-900/80 px-5 py-3 text-sm font-medium text-white backdrop-blur transition hover:border-zinc-500 hover:bg-zinc-800"
                                    >
                                        Open Steam Profile
                                    </a>

                                    <a
                                        href="/wardrobe"
                                        class="inline-flex rounded-xl border border-zinc-700 bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                                    >
                                        Open Wardrobe
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="equippedByType.profile_showcase?.image_url"
                        class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900"
                    >
                        <img
                            :src="equippedByType.profile_showcase.image_url"
                            :alt="equippedByType.profile_showcase.name"
                            class="max-h-[420px] w-full object-cover"
                        />
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
                            <div class="absolute right-5 top-3 text-7xl text-zinc-800">
                                ▦
                            </div>

                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
                                Total Library
                            </p>

                            <div class="mt-4 flex items-end gap-2">
                                <p class="text-5xl font-bold">
                                    {{ games.length }}
                                </p>

                                <p class="pb-1 text-sm text-zinc-400">
                                    games
                                </p>
                            </div>

                            <p class="mt-6 text-xs text-zinc-500">
                                Steam + custom games
                            </p>
                        </div>

                        <div class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
                            <div class="absolute right-5 top-2 text-7xl text-zinc-800">
                                ✓
                            </div>

                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
                                Completed Games
                            </p>

                            <div class="mt-4 flex items-end gap-2">
                                <p class="text-5xl font-bold">
                                    {{ stats.finished }}
                                </p>

                                <p class="pb-1 text-sm text-zinc-400">
                                    games
                                </p>
                            </div>

                            <p class="mt-6 text-xs text-zinc-500">
                                {{ stats.played }} played titles
                            </p>
                        </div>

                        <div class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
                                Completion Rate
                            </p>

                            <div class="mt-6 h-2 overflow-hidden rounded-full bg-zinc-800">
                                <div
                                    class="h-full rounded-full bg-white transition-all"
                                    :style="{
                                        width: `${stats.completionRate}%`,
                                    }"
                                />
                            </div>

                            <p class="mt-6 text-5xl font-bold">
                                {{ stats.completionRate }}%
                            </p>

                            <p class="mt-6 text-xs text-zinc-500">
                                {{ stats.finished }}
                                out of
                                {{ games.length }}
                                games
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-8 xl:grid-cols-[400px,1fr]">
                        <div class="rounded-3xl border border-zinc-800 bg-zinc-900">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between p-6 text-left transition hover:bg-zinc-800/60"
                                @click="openActivity = !openActivity"
                            >
                                <div>
                                    <h2 class="text-xl font-semibold">
                                        Recent Activity
                                    </h2>

                                    <p class="mt-1 text-sm text-zinc-500">
                                        {{ activity.length }}
                                        recent updates
                                    </p>
                                </div>

                                <span class="text-xl text-zinc-500">
                                    {{ openActivity ? '−' : '+' }}
                                </span>
                            </button>

                            <div
                                v-if="openActivity"
                                class="space-y-4 border-t border-zinc-800 p-6"
                            >
                                <div
                                    v-for="item in activity"
                                    :key="item.id"
                                    class="flex gap-4 rounded-2xl border border-zinc-800 bg-zinc-950 p-4"
                                >
                                    <img
                                        v-if="item.cover_url"
                                        :src="item.cover_url"
                                        class="h-20 w-14 rounded-lg object-cover"
                                    />

                                    <div class="min-w-0 flex-1">
                                        <h3 class="truncate font-semibold">
                                            {{ item.title }}
                                        </h3>

                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <span
                                                class="rounded-lg px-2 py-1 text-xs text-white"
                                                :style="{
                                                    backgroundColor: statusColor(item),
                                                }"
                                            >
                                                {{ normalizeStatus(item.status) }}
                                            </span>

                                            <span
                                                v-if="item.rating"
                                                class="rounded-lg bg-yellow-500/10 px-2 py-1 text-xs text-yellow-400"
                                            >
                                                {{ ratingStars(item.rating) }}
                                            </span>

                                            <span
                                                v-if="item.recommended"
                                                class="rounded-lg bg-green-500/10 px-2 py-1 text-xs text-green-400"
                                            >
                                                Recommended
                                            </span>
                                        </div>

                                        <p
                                            v-if="item.note"
                                            class="mt-3 line-clamp-2 text-sm text-zinc-400"
                                        >
                                            {{ item.note }}
                                        </p>

                                        <p class="mt-3 text-xs text-zinc-600">
                                            {{ item.updated_at }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
                            <div class="mb-6 flex items-center justify-between">
                                <h2 class="text-xl font-semibold">
                                    Games Library
                                </h2>

                                <p class="text-sm text-zinc-500">
                                    {{ games.length }}
                                    games
                                </p>
                            </div>

                            <div class="space-y-4">
                                <div
                                    v-for="(statusGames, status) in groupedGames"
                                    :key="status"
                                    class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950"
                                >
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-zinc-900"
                                        @click="toggleStatus(status)"
                                    >
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="h-3 w-3 rounded-full"
                                                :style="{
                                                    backgroundColor: statusColor(statusGames[0]),
                                                }"
                                            />

                                            <div>
                                                <h3 class="font-semibold text-white">
                                                    {{ status }}
                                                </h3>

                                                <p class="text-sm text-zinc-500">
                                                    {{ statusGames.length }}
                                                    games
                                                </p>
                                            </div>
                                        </div>

                                        <span class="text-xl text-zinc-500">
                                            {{ openStatuses[status] ? '−' : '+' }}
                                        </span>
                                    </button>

                                    <div
                                        v-if="openStatuses[status]"
                                        class="grid gap-4 border-t border-zinc-800 p-4 md:grid-cols-2 2xl:grid-cols-3"
                                    >
                                        <div
                                            v-for="game in statusGames"
                                            :key="game.id"
                                            class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900"
                                        >
                                            <img
                                                v-if="game.cover_url"
                                                :src="game.cover_url"
                                                class="h-48 w-full object-cover"
                                            />

                                            <div class="p-4">
                                                <h3 class="truncate font-semibold">
                                                    {{ game.title ?? game.name }}
                                                </h3>

                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    <span
                                                        class="rounded-lg px-2 py-1 text-xs text-white"
                                                        :style="{
                                                            backgroundColor: statusColor(game),
                                                        }"
                                                    >
                                                        {{ normalizeStatus(game.status) }}
                                                    </span>

                                                    <span
                                                        v-if="game.rating"
                                                        class="rounded-lg bg-yellow-500/10 px-2 py-1 text-xs text-yellow-400"
                                                    >
                                                        {{ ratingStars(game.rating) }}
                                                    </span>
                                                </div>

                                                <p
                                                    v-if="game.note"
                                                    class="mt-3 line-clamp-3 text-sm text-zinc-400"
                                                >
                                                    {{ game.note }}
                                                </p>

                                                <p class="mt-4 text-xs text-zinc-600">
                                                    Last activity:
                                                    {{ game.updated_at ?? 'No updates' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>
</template>