<script setup>
defineProps({
    loading: {
        type: Boolean,
        default: false,
    },

    steamResults: {
        type: Array,
        default: () => [],
    },

    igdbResults: {
        type: Array,
        default: () => [],
    },
})

defineEmits([
    'selectSteam',
    'selectIgdb',
])
</script>

<template>
    <section class="space-y-6">
        <p
            v-if="loading"
            class="text-sm text-zinc-500"
        >
            Searching Steam and IGDB...
        </p>

        <div
            v-if="steamResults.length || igdbResults.length"
            class="grid gap-6"
            :class="steamResults.length && igdbResults.length ? 'xl:grid-cols-2' : 'grid-cols-1'"
        >
            <div
                v-if="steamResults.length"
                class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-5"
            >
                <h2 class="mb-4 text-lg font-bold text-white">
                    Steam matches
                </h2>

                <div class="space-y-3">
                    <button
                        v-for="game in steamResults"
                        :key="game.appid"
                        type="button"
                        class="w-full rounded-2xl border border-zinc-800 bg-zinc-950 p-4 text-left transition hover:border-zinc-600"
                        @click="$emit('selectSteam', game)"
                    >
                        <div class="flex items-center gap-4">
                            <img
                                v-if="game.cover_url"
                                :src="game.cover_url"
                                :alt="game.title"
                                class="h-16 w-28 rounded-xl object-cover"
                            >

                            <div
                                v-else
                                class="flex h-16 w-28 items-center justify-center rounded-xl bg-zinc-800 text-xs text-zinc-500"
                            >
                                Steam
                            </div>

                            <div>
                                <p class="font-semibold text-white">
                                    {{ game.title }}
                                </p>

                                <p class="text-sm text-zinc-500">
                                    Steam App ID: {{ game.appid }}
                                </p>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <div
                v-if="igdbResults.length"
                class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-5"
            >
                <h2 class="mb-4 text-lg font-bold text-white">
                    IGDB matches
                </h2>

                <div class="space-y-3">
                    <button
                        v-for="game in igdbResults"
                        :key="game.igdb_id"
                        type="button"
                        class="w-full rounded-2xl border border-zinc-800 bg-zinc-950 p-4 text-left transition hover:border-zinc-600"
                        @click="$emit('selectIgdb', game)"
                    >
                        <div class="flex items-center gap-4">
                            <img
                                v-if="game.igdb_cover_url || game.cover_url"
                                :src="game.igdb_cover_url || game.cover_url"
                                :alt="game.title"
                                class="h-16 w-28 rounded-xl object-cover"
                            >

                            <div
                                v-else
                                class="flex h-16 w-28 items-center justify-center rounded-xl bg-zinc-800 text-xs text-zinc-500"
                            >
                                IGDB
                            </div>

                            <div>
                                <p class="font-semibold text-white">
                                    {{ game.title }}
                                </p>

                                <p class="text-sm text-zinc-500">
                                    IGDB ID: {{ game.igdb_id }}
                                </p>

                                <p
                                    v-if="game.release_date"
                                    class="text-xs text-zinc-600"
                                >
                                    {{ game.release_date }}
                                </p>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>