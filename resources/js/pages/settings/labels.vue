<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },

    labels: {
        type: Array,
        default: () => [],
    },
})

const title = ref('')
const color = ref('#3b82f6')

const bannedWords = [
    'hitler',
    'nazi',
    '1488',
    'heilhitler',
    'nigger',
    'nigga',
    'nigg',
    'kike',
    'chink',
    'spic',
    'faggot',
    'tranny',
    'isis',
    'alqaeda',
    'pedo',
    'pedophile',
]

const normalizedTitle = computed(() =>
    title.value
        .toLowerCase()
        .trim()
)

const hasBadWord = computed(() =>
    bannedWords.some((word) =>
        normalizedTitle.value.includes(word)
    )
)

const duplicate = computed(() =>
    props.labels.some((label) =>
        label.title.toLowerCase().trim() ===
        normalizedTitle.value
    )
)

const canSubmit = computed(() =>
    title.value.trim().length >= 2 &&
    !hasBadWord.value &&
    !duplicate.value
)

function submit() {
    if (!canSubmit.value) {
        return
    }

    router.post('/settings/labels', {
        title: title.value.trim(),
        color: color.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            title.value = ''
            color.value = '#3b82f6'
        },
    })
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="mx-auto w-full max-w-5xl flex-1 p-8">
                <div class="mb-10">
                    <h1 class="text-4xl font-bold text-white">
                        Custom Labels
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Create your own labels with a title and color.
                    </p>
                </div>

                <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
                    <section
                        class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-6"
                    >
                        <h2 class="text-xl font-bold text-white">
                            Your labels
                        </h2>

                        <div
                            v-if="labels.length"
                            class="mt-6 space-y-3"
                        >
                            <div
                                v-for="label in labels"
                                :key="label.id"
                                class="flex items-center justify-between rounded-2xl border border-zinc-800 bg-zinc-950 p-4"
                            >
                                <div class="flex items-center gap-3">
                                    <span
                                        class="h-4 w-4 rounded-full"
                                        :style="{ backgroundColor: label.color }"
                                    />

                                    <p class="font-semibold text-white">
                                        {{ label.title }}
                                    </p>
                                </div>

                                <span class="text-sm text-zinc-500">
                                    {{ label.color }}
                                </span>
                            </div>
                        </div>

                        <p
                            v-else
                            class="mt-6 text-sm text-zinc-500"
                        >
                            You do not have any custom labels yet.
                        </p>
                    </section>

                    <aside
                        class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-6"
                    >
                        <h2 class="text-xl font-bold text-white">
                            Add label
                        </h2>

                        <div class="mt-6 space-y-5">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-300">
                                    Title
                                </label>

                                <input
                                    v-model="title"
                                    type="text"
                                    placeholder="e.g. Cozy games"
                                    class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                                />

                                <p
                                    v-if="hasBadWord"
                                    class="mt-2 text-sm font-medium text-red-400"
                                >
                                    This label contains a blocked word.
                                </p>

                                <p
                                    v-if="duplicate"
                                    class="mt-2 text-sm font-medium text-red-400"
                                >
                                    This label already exists.
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-300">
                                    Color
                                </label>

                                <div class="flex items-center gap-3">
                                    <input
                                        v-model="color"
                                        type="color"
                                        class="h-12 w-16 cursor-pointer rounded-xl border border-zinc-800 bg-zinc-950 p-1"
                                    />

                                    <input
                                        v-model="color"
                                        type="text"
                                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-white outline-none focus:border-zinc-600"
                                    />
                                </div>
                            </div>

                            <div
                                class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4"
                            >
                                <p class="mb-3 text-sm text-zinc-500">
                                    Preview
                                </p>

                                <div
                                    class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold text-white"
                                    :style="{ backgroundColor: color }"
                                >
                                    {{ title || 'Label title' }}
                                </div>
                            </div>

                            <button
                                type="button"
                                :disabled="!canSubmit"
                                class="w-full rounded-xl bg-white px-5 py-3 text-sm font-bold text-black transition hover:bg-zinc-200 disabled:cursor-not-allowed disabled:opacity-40"
                                @click="submit"
                            >
                                Add label
                            </button>
                        </div>
                    </aside>
                </div>
            </main>
        </div>
    </div>
</template>