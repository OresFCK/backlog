<script setup>
import { ThumbsUp, X } from 'lucide-vue-next'

defineProps({
    rating: {
        type: String,
        default: '',
    },

    status: {
        type: String,
        default: '',
    },

    statuses: {
        type: Array,
        default: () => [],
    },

    recommended: {
        type: Boolean,
        default: false,
    },

    notRecommended: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits([
    'update:rating',
    'update:status',
    'save',
    'toggle-recommended',
    'toggle-not-recommended',
])

const setRating = (value) => {
    emit('update:rating', String(value))
    emit('save')
}
</script>

<template>
    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-6">
        <p class="text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">
            Your Rating
        </p>

        <div class="mt-6 grid grid-cols-5 gap-2">
            <button
                v-for="value in 10"
                :key="value"
                type="button"
                class="rounded-xl border py-3 text-sm font-bold transition"
                :class="
                    Number(rating) === value
                        ? 'border-white bg-white text-zinc-950'
                        : 'border-zinc-700 bg-zinc-900 text-zinc-300 hover:border-zinc-500 hover:text-white'
                "
                @click="setRating(value)"
            >
                {{ value }}
            </button>
        </div>

        <button
            type="button"
            class="mt-6 flex w-full items-center justify-center gap-2 text-sm font-semibold transition"
            :class="
                recommended
                    ? 'text-emerald-300'
                    : 'text-zinc-300 hover:text-white'
            "
            @click="$emit('toggle-recommended')"
        >
            <ThumbsUp class="h-4 w-4" />
            {{ recommended ? 'Recommended' : 'Recommend' }}
        </button>

        <button
            type="button"
            class="mt-3 flex w-full items-center justify-center gap-2 text-sm font-semibold transition"
            :class="
                notRecommended
                    ? 'text-red-300'
                    : 'text-zinc-300 hover:text-white'
            "
            @click="$emit('toggle-not-recommended')"
        >
            <X class="h-4 w-4" />
            {{ notRecommended ? 'Not Recommended' : 'Do Not Recommend' }}
        </button>

        <select
            :value="status"
            class="mt-6 w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-sm font-semibold text-white outline-none focus:border-zinc-600"
            @change="
                $emit('update:status', $event.target.value);
                $emit('save')
            "
        >
            <option
                v-for="item in statuses"
                :key="item.id"
                :value="item.name"
            >
                {{ item.name }}
            </option>
        </select>
    </div>
</template>