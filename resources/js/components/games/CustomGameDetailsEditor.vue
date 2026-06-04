<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    game: {
        type: Object,
        required: true,
    },
})

const isOpen = ref(false)

const form = ref({
    title: props.game.title ?? '',
    publisher: props.game.publisher ?? '',
    developer: props.game.developer ?? '',
    description: props.game.description ?? '',
    release_date: props.game.release_date ?? '',
    playtime_hours: props.game.playtime_hours ?? '',
    cover_url: props.game.cover_url ?? '',
    header_image_url: props.game.header_image_url ?? props.game.header_image ?? '',
    igdb_url: props.game.igdb_url ?? '',
    platform: props.game.platform ?? '',
})

const save = () => {
    router.patch(
        `/custom-games/${props.game.custom_game_id}`,
        form.value,
        {
            preserveScroll: true,
        }
    )
}
</script>

<template>
    <div
        v-if="game.is_custom"
        class="rounded-3xl border border-zinc-800 bg-zinc-950"
    >
        <button
            type="button"
            class="flex w-full items-center justify-between p-6 text-left"
            @click="isOpen = !isOpen"
        >
            <div>
                <h2 class="text-xl font-bold text-white">
                    Edit custom game details
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    Fill missing metadata for non-Steam games.
                </p>
            </div>

            <svg
                class="h-5 w-5 text-zinc-400 transition"
                :class="{ 'rotate-180': isOpen }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                />
            </svg>
        </button>

        <div
            v-if="isOpen"
            class="border-t border-zinc-800 p-6"
        >
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-300">
                        Title
                    </label>

                    <input
                        v-model="form.title"
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-white outline-none focus:border-zinc-600"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-300">
                        Release date
                    </label>

                    <input
                        v-model="form.release_date"
                        type="date"
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-white outline-none focus:border-zinc-600"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-300">
                        Playtime hours
                    </label>

                    <input
                        v-model="form.playtime_hours"
                        type="number"
                        min="0"
                        step="0.1"
                        placeholder="e.g. 12.5"
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-300">
                        Developer
                    </label>

                    <input
                        v-model="form.developer"
                        placeholder="e.g. Game Freak"
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-300">
                        Publisher
                    </label>

                    <input
                        v-model="form.publisher"
                        placeholder="e.g. Nintendo"
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-300">
                        Platform
                    </label>

                    <input
                        v-model="form.platform"
                        placeholder="e.g. Nintendo Switch"
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-300">
                        IGDB URL
                    </label>

                    <input
                        v-model="form.igdb_url"
                        placeholder="https://www.igdb.com/games/..."
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                    />
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-zinc-300">
                        Cover URL
                    </label>

                    <input
                        v-model="form.cover_url"
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-white outline-none focus:border-zinc-600"
                    />
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-zinc-300">
                        Header image URL
                    </label>

                    <input
                        v-model="form.header_image_url"
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-white outline-none focus:border-zinc-600"
                    />
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-zinc-300">
                        Description
                    </label>

                    <textarea
                        v-model="form.description"
                        rows="6"
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-white outline-none focus:border-zinc-600"
                    />
                </div>
            </div>

            <button
                type="button"
                class="mt-5 rounded-xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                @click="save"
            >
                Save custom details
            </button>
        </div>
    </div>
</template>