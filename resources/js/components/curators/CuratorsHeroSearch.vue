<script setup>
defineProps({
    query: {
        type: String,
        default: '',
    },
})

defineEmits([
    'update:query',
])
</script>

<template>
    <section class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-8">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-zinc-500">
            Community game pages
        </p>

        <h1 class="mt-3 text-4xl font-bold text-white">
            Curators
        </h1>

        <p class="mt-3 max-w-2xl text-zinc-400">
            Search a game and see community ratings, reviews and curator recommendations.
        </p>

        <div class="mt-8">
            <label class="text-sm font-bold text-zinc-300">
                Search game
            </label>

            <input
                :value="query"
                type="text"
                class="mt-2 w-full rounded-2xl border border-zinc-800 bg-zinc-950 px-5 py-4 text-white outline-none transition placeholder:text-zinc-600 focus:border-zinc-600"
                placeholder="Search Steam or IGDB..."
                @input="$emit('update:query', $event.target.value)"
            >
        </div>
    </section>
</template>