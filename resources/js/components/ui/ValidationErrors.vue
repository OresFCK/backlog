<script setup>
import { computed, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { AlertCircle, X } from 'lucide-vue-next'

const page = usePage()
const dismissed = ref(false)

const errors = computed(() => Object.values(page.props.errors ?? {}).flat().filter(Boolean))

watch(
    errors,
    (value) => {
        if (value.length) {
            dismissed.value = false
        }
    },
    { deep: true },
)
</script>

<template>
    <div
        v-if="errors.length && !dismissed"
        class="fixed bottom-4 right-4 z-[100] w-[calc(100%-2rem)] max-w-md rounded-2xl border border-red-500/40 bg-zinc-950/95 p-4 text-white shadow-2xl backdrop-blur sm:bottom-6 sm:right-6"
        role="alert"
        aria-live="assertive"
    >
        <div class="flex items-start gap-3">
            <AlertCircle class="mt-0.5 h-5 w-5 shrink-0 text-red-400" />

            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold">Check the form</p>

                <ul class="mt-2 space-y-1 text-sm text-zinc-300">
                    <li v-for="(error, index) in errors.slice(0, 5)" :key="`${error}-${index}`">
                        {{ error }}
                    </li>
                </ul>

                <p v-if="errors.length > 5" class="mt-2 text-xs text-zinc-500">
                    And {{ errors.length - 5 }} more errors.
                </p>
            </div>

            <button
                type="button"
                class="rounded-lg p-1 text-zinc-500 transition hover:bg-zinc-900 hover:text-white"
                aria-label="Dismiss validation errors"
                @click="dismissed = true"
            >
                <X class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>
