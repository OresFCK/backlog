<script setup>
const props = defineProps({
    filters: {
        type: Object,
        required: true,
    },

    shownCount: {
        type: Number,
        default: 0,
    },

    totalCount: {
        type: Number,
        default: 0,
    },
})

const emit = defineEmits([
    'update:filters',
    'clear',
])

const updateFilter = (key, value) => {
    emit('update:filters', {
        ...props.filters,
        [key]: value,
    })
}
</script>

<template>
    <div class="mb-6 rounded-3xl border border-zinc-800 bg-zinc-900 p-5">
        <div class="grid gap-4 md:grid-cols-4">
            <input
                :value="filters.user"
                type="text"
                placeholder="Filter by user"
                class="rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                @input="updateFilter('user', $event.target.value)"
            />

            <input
                :value="filters.game"
                type="text"
                placeholder="Filter by game"
                class="rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                @input="updateFilter('game', $event.target.value)"
            />

            <select
                :value="filters.rating"
                class="rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-600"
                @change="updateFilter('rating', $event.target.value)"
            >
                <option value="">
                    Any rating
                </option>

                <option
                    v-for="rating in 10"
                    :key="rating"
                    :value="rating"
                >
                    {{ rating }}/10
                </option>
            </select>

            <select
                :value="filters.recommendation"
                class="rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-600"
                @change="updateFilter('recommendation', $event.target.value)"
            >
                <option value="">
                    Any recommendation
                </option>

                <option value="recommended">
                    Recommended
                </option>

                <option value="not_recommended">
                    Not Recommended
                </option>
            </select>
        </div>

        <div class="mt-4 flex items-center justify-between gap-3">
            <p class="text-sm text-zinc-500">
                Showing {{ shownCount }} of {{ totalCount }} reviews
            </p>

            <button
                type="button"
                class="rounded-xl border border-zinc-800 px-4 py-2 text-sm font-bold text-zinc-300 transition hover:bg-zinc-950 hover:text-white"
                @click="$emit('clear')"
            >
                Clear filters
            </button>
        </div>
    </div>
</template>