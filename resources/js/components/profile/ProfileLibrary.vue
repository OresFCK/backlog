<script setup>
import { ref } from 'vue'
import { normalizeStatus, ratingStars, statusColor } from '@/lib/profile'

defineProps({
    games: Array,
    groupedGames: Object,
})

const openStatuses = ref({})

const toggleStatus = (status) => {
    openStatuses.value[status] = !openStatuses.value[status]
}
</script>

<template>
    <div class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl font-semibold">
                Games Library
            </h2>

            <p class="text-sm text-zinc-500">
                {{ games.length }} games
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
                            :style="{ backgroundColor: statusColor(statusGames[0]) }"
                        />

                        <div>
                            <h3 class="font-semibold text-white">
                                {{ status }}
                            </h3>

                            <p class="text-sm text-zinc-500">
                                {{ statusGames.length }} games
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
                                    :style="{ backgroundColor: statusColor(game) }"
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
                                Last activity: {{ game.updated_at ?? 'No updates' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>