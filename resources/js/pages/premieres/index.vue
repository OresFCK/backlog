<script setup>
import { computed, ref, watch } from 'vue'

import { Head, Link, router } from '@inertiajs/vue3'

import {
    ChevronDown,
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

    games: {
        type: Array,
        required: true,
    },
})

const searchQuery = ref('')
const openMonths = ref({})

const filteredGames = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()

    if (!query) {
        return props.games
    }

    return props.games.filter((game) => {
        return game.title
            ?.toLowerCase()
            .includes(query)
    })
})

const anticipatedGames = computed(() => {
    return props.games.filter(game => game.is_anticipated)
})

const groupedGames = computed(() => {
    const groups = {}

    filteredGames.value.forEach((game) => {
        const month = game.month_label

        if (!groups[month]) {
            groups[month] = []
        }

        groups[month].push(game)
    })

    return groups
})


const toggleMonth = (month) => {
    openMonths.value[month] = !isMonthOpen(month)
}

const isMonthOpen = (month) => {
    return openMonths.value[month] === true
}

const toggleAnticipated = (game) => {
    router.post(`/premieres/${game.id}/anticipate`, {}, {
        preserveScroll: true,
        preserveState: true,
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
                            placeholder="Search upcoming games..."
                            class="w-full bg-transparent text-sm font-medium text-white outline-none placeholder:text-zinc-500"
                        >
                    </div>
                </div>

                <template v-if="filteredGames.length">
                    <section
                        v-for="(monthGames, month) in groupedGames"
                        :key="month"
                        class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900/40"
                    >
                        <button
                            type="button"
                            class="group flex w-full items-center justify-between gap-6 p-6 text-left transition hover:bg-zinc-900"
                            @click="toggleMonth(month)"
                        >
                            <div>
                                <h2 class="text-2xl font-black text-white group-hover:text-emerald-400">
                                    {{ month }}
                                </h2>

                                <p class="mt-1 text-sm text-zinc-400">
                                    {{ monthGames.length }} upcoming releases
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <span class="rounded-full border border-zinc-700 px-4 py-2 text-sm font-bold text-zinc-300">
                                    {{ isMonthOpen(month) ? 'Hide' : 'Show' }}
                                </span>

                                <ChevronDown
                                    class="h-6 w-6 text-zinc-500 transition-transform duration-200"
                                    :class="isMonthOpen(month) ? 'rotate-180' : ''"
                                />
                            </div>
                        </button>

                        <Transition
                            enter-active-class="transition-all duration-200"
                            leave-active-class="transition-all duration-200"
                        >
                            <div
                                v-if="isMonthOpen(month)"
                                class="border-t border-zinc-800 p-6"
                            >
                                <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6 xl:grid-cols-8">
                                    <Link
                                        v-for="game in monthGames"
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