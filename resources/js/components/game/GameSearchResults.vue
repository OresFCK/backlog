<script setup>
const props = defineProps({
    steamResults: Array,
    igdbResults: Array,
    loading: Boolean,
    duplicate: Boolean,
})

defineEmits([
    'selectSteam',
    'selectIgdb',
])
</script>

<template>
    <section class="space-y-6">
        <slot />

        <p
            v-if="duplicate"
            class="mt-3 text-sm font-medium text-red-400"
        >
            This game already exists in your library.
        </p>

        <div
            v-if="steamResults.length || igdbResults.length"
            class="grid gap-6"
            :class="steamResults.length && igdbResults.length ? 'xl:grid-cols-2' : 'grid-cols-1'"
        >
            <div
                v-if="steamResults.length"
                class="w-full rounded-3xl border border-zinc-800 bg-zinc-900/60 p-5"
            >
                <h2 class="mb-4 text-lg font-bold text-white">
                    Steam matches
                </h2>

                <div class="space-y-3">
                    <button
                        v-for="game in steamResults"
                        :key="game.appid"
                        type="button"
                        class="flex w-full min-w-0 items-center gap-5 rounded-2xl border border-zinc-800 bg-zinc-950 p-4 text-left transition hover:border-zinc-600"
                        @click="$emit('selectSteam', game)"
                    >
                        <img
                            :src="game.cover_url"
                            :alt="game.title"
                            class="h-20 w-36 shrink-0 rounded-xl object-cover"
                        />

                        <div class="min-w-0 flex-1">
                            <p class="line-clamp-2 font-semibold text-white">
                                {{ game.title }}
                            </p>

                            <p class="truncate text-sm text-zinc-500">
                                Steam App ID: {{ game.appid }}
                            </p>
                        </div>
                    </button>
                </div>
            </div>

            <div
                v-if="igdbResults.length"
                class="w-full rounded-3xl border border-zinc-800 bg-zinc-900/60 p-5"
            >
                <h2 class="mb-4 text-lg font-bold text-white">
                    IGDB matches
                </h2>

                <div class="space-y-3">
                    <button
                        v-for="game in igdbResults"
                        :key="game.igdb_id"
                        type="button"
                        class="flex w-full min-w-0 items-center gap-5 rounded-2xl border border-zinc-800 bg-zinc-950 p-4 text-left transition hover:border-zinc-600"
                        @click="$emit('selectIgdb', game)"
                    >
                        <img
                            v-if="game.igdb_cover_url || game.cover_url"
                            :src="game.igdb_cover_url || game.cover_url"
                            :alt="game.title"
                            class="h-20 w-36 shrink-0 rounded-xl object-cover"
                        />

                        <div
                            v-else
                            class="flex h-20 w-36 shrink-0 items-center justify-center rounded-xl bg-zinc-800 text-xs text-zinc-500"
                        >
                            IGDB
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="line-clamp-2 font-semibold text-white">
                                {{ game.title }}
                            </p>

                            <p class="truncate text-sm text-zinc-500">
                                IGDB ID: {{ game.igdb_id }}
                            </p>

                            <p
                                v-if="game.release_date"
                                class="text-xs text-zinc-600"
                            >
                                {{ game.release_date }}
                            </p>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <p
            v-if="loading"
            class="text-sm text-zinc-500"
        >
            Searching Steam and IGDB...
        </p>
    </section>
</template>