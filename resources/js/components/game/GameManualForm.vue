<script setup>
defineProps({
    title: String,
    publisher: String,
    coverUrl: String,
    steamAppId: [String, Number, null],
    igdbId: [String, Number, null],
    duplicate: Boolean,
})

defineEmits([
    'update:title',
    'update:publisher',
    'update:coverUrl',
    'submit',
])
</script>

<template>
    <aside class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-6">
        <h2 class="text-xl font-bold text-white">
            Manual details
        </h2>

        <div class="mt-6 space-y-5">
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-300">
                    Title
                </label>

                <input
                    :value="title"
                    type="text"
                    class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-white outline-none focus:border-zinc-600"
                    @input="$emit('update:title', $event.target.value)"
                />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-300">
                    Publisher
                </label>

                <input
                    :value="publisher"
                    type="text"
                    placeholder="e.g. Nintendo"
                    class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                    @input="$emit('update:publisher', $event.target.value)"
                />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-300">
                    Cover URL
                </label>

                <input
                    :value="coverUrl"
                    type="text"
                    class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-white outline-none focus:border-zinc-600"
                    @input="$emit('update:coverUrl', $event.target.value)"
                />
            </div>

            <img
                v-if="coverUrl"
                :src="coverUrl"
                class="h-48 w-full rounded-2xl object-cover"
            />

            <div
                v-if="steamAppId"
                class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3 text-sm text-emerald-300"
            >
                Selected Steam game: {{ steamAppId }}
            </div>

            <div
                v-if="igdbId"
                class="rounded-xl border border-indigo-500/30 bg-indigo-500/10 p-3 text-sm text-indigo-300"
            >
                Selected IGDB game: {{ igdbId }}
            </div>

            <button
                type="button"
                :disabled="duplicate || !title"
                class="w-full rounded-xl bg-white px-5 py-3 text-sm font-bold text-black transition hover:bg-zinc-200 disabled:cursor-not-allowed disabled:opacity-40"
                @click="$emit('submit')"
            >
                Add game
            </button>
        </div>
    </aside>
</template>