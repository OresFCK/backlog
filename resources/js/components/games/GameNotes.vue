<script setup>
import { computed, nextTick, watch } from 'vue'
import { Sparkles, Star } from 'lucide-vue-next'

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },

    showOnPublicProfile: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits([
    'update:modelValue',
    'update:showOnPublicProfile',
    'save',
])

const maxLength = 255

const normalizedValue = computed(() =>
    String(props.modelValue ?? '').slice(0, maxLength)
)

const charactersLeft = computed(() =>
    maxLength - normalizedValue.value.length
)

const updateNote = (event) => {
    const value = event.target.value.slice(0, maxLength)

    event.target.value = value

    emit('update:modelValue', value)
}

const save = async () => {
    emit('update:modelValue', normalizedValue.value)

    await nextTick()

    emit('save')
}

watch(
    () => props.modelValue,
    (value) => {
        if (String(value ?? '').length > maxLength) {
            emit('update:modelValue', String(value).slice(0, maxLength))
        }
    },
    { immediate: true }
)
</script>

<template>
    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white">
                    Your notes
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    Write your thoughts and choose whether to feature them publicly.
                </p>
            </div>

            <button
                type="button"
                class="rounded-xl border px-4 py-2 text-sm font-semibold transition"
                :class="
                    showOnPublicProfile
                        ? 'border-indigo-500/40 bg-indigo-500/10 text-indigo-300'
                        : 'border-zinc-800 bg-zinc-900 text-zinc-300 hover:border-zinc-700 hover:text-white'
                "
                @click="
                    $emit('update:showOnPublicProfile', !showOnPublicProfile);
                    save();
                "
            >
                <span
                    v-if="showOnPublicProfile"
                    class="flex items-center gap-2"
                >
                    <Star class="h-4 w-4" />
                    Featured on Profile
                </span>

                <span
                    v-else
                    class="flex items-center gap-2"
                >
                    <Sparkles class="h-4 w-4" />
                    Add to Showcase
                </span>
            </button>
        </div>

        <textarea
            :value="normalizedValue"
            :maxlength="maxLength"
            placeholder="Write your thoughts about this game..."
            class="mt-5 min-h-40 w-full resize-none rounded-xl border border-zinc-800 bg-zinc-900 p-4 text-sm text-zinc-200 outline-none placeholder:text-zinc-500 focus:border-zinc-600"
            @input="updateNote"
            @blur="save"
        />

        <div class="mt-3 flex items-center justify-between gap-4">
            <p
                v-if="showOnPublicProfile"
                class="text-sm font-medium text-indigo-300"
            >
                This game is visible on your public profile.
            </p>

            <span
                class="ml-auto text-xs font-semibold"
                :class="
                    charactersLeft <= 20
                        ? 'text-red-400'
                        : 'text-zinc-500'
                "
            >
                {{ charactersLeft }} characters left
            </span>
        </div>
    </div>
</template>