<script setup>
import { ref } from 'vue'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

defineProps({
    user: Object,

    stats: {
        type: Object,
        required: true,
    },
})

const activeTab = ref('games')
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 space-y-8 p-8">
                <section class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-8">
                    <h1 class="text-3xl font-bold text-white">
                        Stats
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Your games, challenges and wardrobe overview.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button
                            type="button"
                            class="rounded-2xl px-5 py-3 text-sm font-bold transition"
                            :class="activeTab === 'games'
                                ? 'bg-white text-zinc-950'
                                : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700'"
                            @click="activeTab = 'games'"
                        >
                            Games
                        </button>

                        <button
                            type="button"
                            class="rounded-2xl px-5 py-3 text-sm font-bold transition"
                            :class="activeTab === 'challenges'
                                ? 'bg-white text-zinc-950'
                                : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700'"
                            @click="activeTab = 'challenges'"
                        >
                            Challenges
                        </button>

                        <button
                            type="button"
                            class="rounded-2xl px-5 py-3 text-sm font-bold transition"
                            :class="activeTab === 'wardrobe'
                                ? 'bg-white text-zinc-950'
                                : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700'"
                            @click="activeTab = 'wardrobe'"
                        >
                            Wardrobe
                        </button>
                    </div>
                </section>

                <template v-if="activeTab === 'games'">
                    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-3xl border border-zinc-800 bg-black p-6">
                            <p class="text-sm font-bold uppercase tracking-wide text-zinc-500">
                                Total games
                            </p>

                            <p class="mt-4 text-4xl font-black text-white">
                                {{ stats.games.total_games }}
                            </p>
                        </div>

                        <div class="rounded-3xl border border-zinc-800 bg-black p-6">
                            <p class="text-sm font-bold uppercase tracking-wide text-zinc-500">
                                Played games
                            </p>

                            <p class="mt-4 text-4xl font-black text-white">
                                {{ stats.games.played_games }}
                            </p>
                        </div>

                        <div class="rounded-3xl border border-zinc-800 bg-black p-6">
                            <p class="text-sm font-bold uppercase tracking-wide text-zinc-500">
                                Total playtime
                            </p>

                            <p class="mt-4 text-4xl font-black text-white">
                                {{ stats.games.total_playtime_hours }}h
                            </p>
                        </div>

                        <div class="rounded-3xl border border-zinc-800 bg-black p-6">
                            <p class="text-sm font-bold uppercase tracking-wide text-zinc-500">
                                Average playtime
                            </p>

                            <p class="mt-4 text-4xl font-black text-white">
                                {{ stats.games.average_playtime_hours }}h
                            </p>
                        </div>
                    </section>

                    <section class="grid gap-8 xl:grid-cols-2">
                        <div class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-6">
                            <h2 class="text-xl font-bold text-white">
                                Backlog progress
                            </h2>

                            <p class="mt-2 text-sm text-zinc-500">
                                Steam games with 100% achievements are counted as finished.
                            </p>

                            <div class="mt-6 space-y-5">
                                <div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-zinc-400">Finished</span>
                                        <span class="font-bold text-white">
                                            {{ stats.games.completion_summary.finished_percent }}%
                                        </span>
                                    </div>

                                    <div class="mt-2 h-3 overflow-hidden rounded-full bg-zinc-800">
                                        <div
                                            class="h-full rounded-full bg-emerald-400"
                                            :style="{ width: `${stats.games.completion_summary.finished_percent}%` }"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-zinc-400">Playing</span>
                                        <span class="font-bold text-white">
                                            {{ stats.games.completion_summary.playing_percent }}%
                                        </span>
                                    </div>

                                    <div class="mt-2 h-3 overflow-hidden rounded-full bg-zinc-800">
                                        <div
                                            class="h-full rounded-full bg-indigo-400"
                                            :style="{ width: `${stats.games.completion_summary.playing_percent}%` }"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-zinc-400">Dropped</span>
                                        <span class="font-bold text-white">
                                            {{ stats.games.completion_summary.dropped_percent }}%
                                        </span>
                                    </div>

                                    <div class="mt-2 h-3 overflow-hidden rounded-full bg-zinc-800">
                                        <div
                                            class="h-full rounded-full bg-red-400"
                                            :style="{ width: `${stats.games.completion_summary.dropped_percent}%` }"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-6">
                            <h2 class="text-xl font-bold text-white">
                                Status breakdown
                            </h2>

                            <div class="mt-6 space-y-3">
                                <div
                                    v-for="item in stats.games.status_breakdown"
                                    :key="item.status"
                                    class="flex items-center justify-between rounded-2xl border border-zinc-800 bg-black px-4 py-3"
                                >
                                    <span class="font-medium text-zinc-300">
                                        {{ item.status }}
                                    </span>

                                    <span class="font-black text-white">
                                        {{ item.count }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="grid gap-8 xl:grid-cols-[1fr_360px]">
                        <div class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-6">
                            <h2 class="text-xl font-bold text-white">
                                Top playtime games
                            </h2>

                            <div class="mt-6 space-y-3">
                                <div
                                    v-for="game in stats.games.top_playtime_games"
                                    :key="game.id"
                                    class="flex items-center gap-4 rounded-2xl border border-zinc-800 bg-black p-3"
                                >
                                    <img
                                        v-if="game.cover_url"
                                        :src="game.cover_url"
                                        :alt="game.title"
                                        class="h-16 w-28 rounded-xl object-cover"
                                    />

                                    <div
                                        v-else
                                        class="flex h-16 w-28 items-center justify-center rounded-xl bg-zinc-800 text-xs text-zinc-500"
                                    >
                                        No cover
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-bold text-white">
                                            {{ game.title }}
                                        </p>

                                        <p class="text-sm text-zinc-500">
                                            {{ game.platform }}
                                            <span v-if="game.achievement_percent">
                                                · {{ game.achievement_percent }}% achievements
                                            </span>
                                        </p>
                                    </div>

                                    <p class="text-lg font-black text-white">
                                        {{ game.playtime_hours }}h
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-8">
                            <div class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-6">
                                <h2 class="text-xl font-bold text-white">
                                    Platforms
                                </h2>

                                <div class="mt-6 space-y-3">
                                    <div
                                        v-for="item in stats.games.platform_breakdown"
                                        :key="item.platform"
                                        class="flex items-center justify-between rounded-2xl border border-zinc-800 bg-black px-4 py-3"
                                    >
                                        <span class="font-medium text-zinc-300">
                                            {{ item.platform }}
                                        </span>

                                        <span class="font-black text-white">
                                            {{ item.count }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-6">
                                <h2 class="text-xl font-bold text-white">
                                    100% achievements
                                </h2>

                                <div class="mt-6 space-y-3">
                                    <div
                                        v-for="game in stats.games.perfected_games"
                                        :key="game.id"
                                        class="rounded-2xl border border-zinc-800 bg-black px-4 py-3"
                                    >
                                        <p class="font-bold text-white">
                                            {{ game.title }}
                                        </p>

                                        <p class="text-sm text-emerald-400">
                                            {{ game.achievement_percent }}% achievements
                                        </p>
                                    </div>

                                    <p
                                        v-if="!stats.games.perfected_games.length"
                                        class="text-sm text-zinc-500"
                                    >
                                        No perfected games yet.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                </template>

                <template v-if="activeTab === 'challenges'">
                    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-3xl border border-zinc-800 bg-black p-6">
                            <p class="text-sm font-bold uppercase tracking-wide text-zinc-500">
                                Available
                            </p>

                            <p class="mt-4 text-4xl font-black text-white">
                                {{ stats.challenges.available }}
                            </p>
                        </div>

                        <div class="rounded-3xl border border-zinc-800 bg-black p-6">
                            <p class="text-sm font-bold uppercase tracking-wide text-zinc-500">
                                Joined
                            </p>

                            <p class="mt-4 text-4xl font-black text-white">
                                {{ stats.challenges.joined }}
                            </p>
                        </div>

                        <div class="rounded-3xl border border-zinc-800 bg-black p-6">
                            <p class="text-sm font-bold uppercase tracking-wide text-zinc-500">
                                Completed
                            </p>

                            <p class="mt-4 text-4xl font-black text-white">
                                {{ stats.challenges.completed }}
                            </p>
                        </div>

                        <div class="rounded-3xl border border-zinc-800 bg-black p-6">
                            <p class="text-sm font-bold uppercase tracking-wide text-zinc-500">
                                Completion
                            </p>

                            <p class="mt-4 text-4xl font-black text-white">
                                {{ stats.challenges.completion_percent }}%
                            </p>
                        </div>
                    </section>

                    <section class="grid gap-8 xl:grid-cols-2">
                        <div class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-6">
                            <h2 class="text-xl font-bold text-white">
                                Submission status
                            </h2>

                            <div class="mt-6 space-y-3">
                                <div class="flex items-center justify-between rounded-2xl border border-zinc-800 bg-black px-4 py-3">
                                    <span class="font-medium text-zinc-300">Pending</span>
                                    <span class="font-black text-amber-400">{{ stats.challenges.pending }}</span>
                                </div>

                                <div class="flex items-center justify-between rounded-2xl border border-zinc-800 bg-black px-4 py-3">
                                    <span class="font-medium text-zinc-300">Approved</span>
                                    <span class="font-black text-emerald-400">{{ stats.challenges.approved }}</span>
                                </div>

                                <div class="flex items-center justify-between rounded-2xl border border-zinc-800 bg-black px-4 py-3">
                                    <span class="font-medium text-zinc-300">Rejected</span>
                                    <span class="font-black text-red-400">{{ stats.challenges.rejected }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-6">
                            <h2 class="text-xl font-bold text-white">
                                Earned rewards
                            </h2>

                            <div class="mt-6 grid gap-4 md:grid-cols-3">
                                <div class="rounded-2xl border border-zinc-800 bg-black p-4">
                                    <p class="text-sm text-zinc-500">XP</p>
                                    <p class="mt-2 text-2xl font-black text-white">
                                        {{ stats.challenges.earned_xp }}
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-zinc-800 bg-black p-4">
                                    <p class="text-sm text-zinc-500">Coins</p>
                                    <p class="mt-2 text-2xl font-black text-white">
                                        {{ stats.challenges.earned_coins }}
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-zinc-800 bg-black p-4">
                                    <p class="text-sm text-zinc-500">Items</p>
                                    <p class="mt-2 text-2xl font-black text-white">
                                        {{ stats.challenges.earned_items }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                </template>

                <template v-if="activeTab === 'wardrobe'">
                    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-3xl border border-zinc-800 bg-black p-6">
                            <p class="text-sm font-bold uppercase tracking-wide text-zinc-500">
                                Owned items
                            </p>

                            <p class="mt-4 text-4xl font-black text-white">
                                {{ stats.wardrobe.owned_items }}
                            </p>
                        </div>

                        <div class="rounded-3xl border border-zinc-800 bg-black p-6">
                            <p class="text-sm font-bold uppercase tracking-wide text-zinc-500">
                                Equipped
                            </p>

                            <p class="mt-4 text-4xl font-black text-white">
                                {{ stats.wardrobe.equipped_items }}
                            </p>
                        </div>

                        <div class="rounded-3xl border border-zinc-800 bg-black p-6">
                            <p class="text-sm font-bold uppercase tracking-wide text-zinc-500">
                                Featured
                            </p>

                            <p class="mt-4 text-4xl font-black text-white">
                                {{ stats.wardrobe.featured_items }}
                            </p>
                        </div>

                        <div class="rounded-3xl border border-zinc-800 bg-black p-6">
                            <p class="text-sm font-bold uppercase tracking-wide text-zinc-500">
                                Collection value
                            </p>

                            <p class="mt-4 text-4xl font-black text-white">
                                {{ stats.wardrobe.collection_value }}
                            </p>
                        </div>
                    </section>

                    <section class="grid gap-8 xl:grid-cols-2">
                        <div class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-6">
                            <h2 class="text-xl font-bold text-white">
                                Items by type
                            </h2>

                            <div class="mt-6 space-y-3">
                                <div
                                    v-for="item in stats.wardrobe.type_breakdown"
                                    :key="item.type"
                                    class="flex items-center justify-between rounded-2xl border border-zinc-800 bg-black px-4 py-3"
                                >
                                    <span class="font-medium text-zinc-300">
                                        {{ item.type }}
                                    </span>

                                    <span class="font-black text-white">
                                        {{ item.count }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-6">
                            <h2 class="text-xl font-bold text-white">
                                Featured on profile
                            </h2>

                            <div class="mt-6 space-y-3">
                                <div
                                    v-for="item in stats.wardrobe.featured"
                                    :key="item.id"
                                    class="rounded-2xl border border-zinc-800 bg-black px-4 py-3"
                                >
                                    <p class="font-bold text-white">
                                        {{ item.name }}
                                    </p>

                                    <p class="text-sm text-zinc-500">
                                        {{ item.type }}
                                    </p>
                                </div>

                                <p
                                    v-if="!stats.wardrobe.featured.length"
                                    class="text-sm text-zinc-500"
                                >
                                    No featured wardrobe items yet.
                                </p>
                            </div>
                        </div>
                    </section>
                </template>
            </main>
        </div>
    </div>
</template>