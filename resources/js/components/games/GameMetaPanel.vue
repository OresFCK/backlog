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

const blockInvalidKeys = (event) => {
    const allowedKeys = [
        'Backspace',
        'Delete',
        'ArrowLeft',
        'ArrowRight',
        'Tab',
    ]

    if (allowedKeys.includes(event.key)) {
        return
    }

    if (!/^\d$/.test(event.key)) {
        event.preventDefault()
    }
}

const normalizeRating = (value) => {
    let normalizedValue = value.replace(/\D/g, '').slice(0, 2)

    if (normalizedValue === '') {
        return ''
    }

    if (Number(normalizedValue) > 10) {
        return '10'
    }

    if (Number(normalizedValue) < 1) {
        return '1'
    }

    return normalizedValue
}

const handleRatingInput = (event) => {
    emit('update:rating', normalizeRating(event.target.value))
}
</script>

<template>
    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-6 text-center">
        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">
            Your Rating
        </p>

        <div class="mx-auto mt-6 flex h-36 w-36 items-center justify-center rounded-full border-[10px] border-zinc-200">
            <div class="flex items-center gap-1">
                <input
                    :value="rating"
                    type="text"
                    inputmode="numeric"
                    maxlength="2"
                    placeholder="—"
                    class="w-14 bg-transparent text-center text-4xl font-black text-white outline-none"
                    @keydown="blockInvalidKeys"
                    @input="handleRatingInput"
                    @blur="$emit('save')"
                />

                <span class="text-2xl font-bold text-white">
                    /10
                </span>
            </div>
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