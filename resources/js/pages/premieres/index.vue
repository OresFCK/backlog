<script setup>
import { computed, ref } from 'vue'

import { Head, Link, router } from '@inertiajs/vue3'

import {
    ChevronDown,
    Loader2,
    Search,
    Star,
} from 'lucide-vue-next'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },

    months: {
        type: Array,
        required: true,
    },

    anticipatedGames: {
        type: Array,
        required: true,
    },
})

const searchQuery = ref('')
const openMonths = ref({})
const loadedMonths = ref({})
const loadingMonths = ref({})

const filteredMonths = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()

    if (!query) {
        return props.months
    }

    return props.months.filter((month) => {
        const games = loadedMonths.value[month.month] ?? []

        return month.label.toLowerCase().includes(query)
            || games.some((game) => game.title?.toLowerCase().includes(query))
    })
})

const isMonthOpen = (month) => {
    return openMonths.value[month] === true
}

const isMonthLoading = (month) => {
    return loadingMonths.value[month] === true
}

const monthGames = (month) => {
    const games = loadedMonths.value[month] ?? []
    const query = searchQuery.value.trim().toLowerCase()

    if (!query) {
        return games
    }

    return games.filter((game) => {
        return game.title
            ?.toLowerCase()
            .includes(query)
    })
}

const loadMonth = async (month) => {
    if (loadedMonths.value[month] || loadingMonths.value[month]) {
        return
    }

    loadingMonths.value[month] = true

    try {
        const response = await fetch(`/premieres/month/${month}`, {
            headers: {
                Accept: 'application/json',
            },
        })

        if (!response.ok) {
            throw new Error('Failed to load month')
        }

        const data = await response.json()

        loadedMonths.value[month] = data.games ?? []
    } finally {
        loadingMonths.value[month] = false
    }
}

const toggleMonth = async (month) => {
    openMonths.value[month] = !isMonthOpen(month)

    if (isMonthOpen(month)) {
        await loadMonth(month)
    }
}

const toggleAnticipated = (game) => {
    router.post(`/premieres/${game.id}/anticipate`, {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            Object.keys(loadedMonths.value).forEach((month) => {
                loadedMonths.value[month] = loadedMonths.value[month].map((item) => {
                    if (item.id !== game.id) {
                        return item
                    }

                    return {
                        ...item,
                        is_anticipated: !item.is_anticipated,
                    }
                })
            })
        },
    })
}
</script>

<template>
    <Head title="Premieres" />

    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 space-y-8 p-8">
                <div>
                    <h1 class="text-4xl font-black text-white">
                        Upcoming Releases
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Discover games releasing soon according to IGDB.
                    </p>
                </div>

                <section
                    v-if="anticipatedGames.length"
                    class="rounded-3xl border border-emerald-500/30 bg-emerald-500/5 p-6"
                >
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-400">
                            Your most anticipated
                        </p>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-3 lg:grid-cols-6 xl:grid-cols-8">
                        <Link
                            v-for="game in anticipatedGames"
                            :key="game.id"
                            :href="`/${game.slug}`"
                            class="group overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950 transition-all duration-200 hover:border-emerald-500/50 hover:bg-zinc-900"
                        >
                            <div class="aspect-[3/4] overflow-hidden bg-zinc-800">
                                <img
                                    :src="game.igdb_cover_url"
                                    :alt="game.title"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                >
                            </div>

                            <div class="p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-400">
                                    {{ game.formatted_release_date }}
                                </p>

                                <h3 class="mt-1 line-clamp-2 text-sm font-bold leading-snug text-white group-hover:text-emerald-400">
                                    {{ game.title }}
                                </h3>
                            </div>
                        </Link>
                    </div>
                </section>

                <div class="rounded-3xl border border-zinc-800 bg-zinc-900/40 p-5">
                    <div class="flex items-center gap-4">
                        <Search class="h-5 w-5 text-zinc-500" />

                        <input
                            v-model="searchQuery"
                            type="search"
                            placeholder="Search loaded upcoming games..."
                            class="w-full bg-transparent text-sm font-medium text-white outline-none placeholder:text-zinc-500"
                        >
                    </div>
                </div>

                <template v-if="filteredMonths.length">
                    <section
                        v-for="month in filteredMonths"
                        :key="month.month"
                        class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900/40"
                    >
                        <button
                            type="button"
                            class="group flex w-full items-center justify-between gap-6 p-6 text-left transition hover:bg-zinc-900"
                            @click="toggleMonth(month.month)"
                        >
                            <div>
                                <h2 class="text-2xl font-black text-white group-hover:text-emerald-400">
                                    {{ month.label }}
                                </h2>

                                <p class="mt-1 text-sm text-zinc-400">
                                    {{ month.total }} upcoming releases
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <Loader2
                                    v-if="isMonthLoading(month.month)"
                                    class="h-5 w-5 animate-spin text-zinc-500"
                                />

                                <span class="rounded-full border border-zinc-700 px-4 py-2 text-sm font-bold text-zinc-300">
                                    {{ isMonthOpen(month.month) ? 'Hide' : 'Show' }}
                                </span>

                                <ChevronDown
                                    class="h-6 w-6 text-zinc-500 transition-transform duration-200"
                                    :class="isMonthOpen(month.month) ? 'rotate-180' : ''"
                                />
                            </div>
                        </button>

                        <Transition
                            enter-active-class="transition-all duration-200"
                            leave-active-class="transition-all duration-200"
                        >
                            <div
                                v-if="isMonthOpen(month.month)"
                                class="border-t border-zinc-800 p-6"
                            >
                                <div
                                    v-if="isMonthLoading(month.month)"
                                    class="py-10 text-center text-sm font-semibold text-zinc-500"
                                >
                                    Loading releases...
                                </div>

                                <div
                                    v-else-if="monthGames(month.month).length"
                                    class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6 xl:grid-cols-8"
                                >
                                    <Link
                                        v-for="game in monthGames(month.month)"
                                        :key="game.id"
                                        :href="`/${game.slug}`"
                                        class="group relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950 transition-all duration-200 hover:border-zinc-700 hover:bg-zinc-900"
                                    >
                                        <button
                                            type="button"
                                            class="absolute right-2 top-2 z-10 rounded-full border border-white/10 bg-black/70 p-2 text-white backdrop-blur transition hover:bg-emerald-500"
                                            :class="game.is_anticipated ? 'bg-emerald-500 text-white' : ''"
                                            @click.prevent.stop="toggleAnticipated(game)"
                                        >
                                            <Star
                                                class="h-4 w-4"
                                                :class="game.is_anticipated ? 'fill-current' : ''"
                                            />
                                        </button>

                                        <div class="aspect-[3/4] overflow-hidden bg-zinc-800">
                                            <img
                                                :src="game.igdb_cover_url"
                                                :alt="game.title"
                                                loading="lazy"
                                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                            >
                                        </div>

                                        <div class="p-3">
                                            <div class="text-[11px] font-semibold uppercase tracking-wider text-emerald-400">
                                                {{ game.formatted_release_date }}
                                            </div>

                                            <h3 class="mt-1 line-clamp-2 text-sm font-bold leading-snug text-white group-hover:text-emerald-400">
                                                {{ game.title }}
                                            </h3>
                                        </div>
                                    </Link>
                                </div>

                                <div
                                    v-else
                                    class="py-10 text-center text-sm font-semibold text-zinc-500"
                                >
                                    No releases found in this month.
                                </div>
                            </div>
                        </Transition>
                    </section>
                </template>

                <div
                    v-else
                    class="rounded-3xl border border-zinc-800 bg-zinc-900/40 p-10 text-center"
                >
                    <p class="text-zinc-400">
                        No upcoming releases found.
                    </p>
                </div>
            </main>
        </div>
    </div>
</template>