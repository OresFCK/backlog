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
const maxTitleLength = 20

const editingId = ref(null)
const editTitle = ref('')
const editColor = ref('#3b82f6')

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

const normalizedEditTitle = computed(() =>
    editTitle.value
        .toLowerCase()
        .trim()
)

const titleCharactersLeft = computed(() =>
    maxTitleLength - title.value.length
)

const editTitleCharactersLeft = computed(() =>
    maxTitleLength - editTitle.value.length
)

const titleTooLong = computed(() =>
    title.value.length > maxTitleLength
)

const editTitleTooLong = computed(() =>
    editTitle.value.length > maxTitleLength
)

const hasBadWord = computed(() =>
    bannedWords.some((word) =>
        normalizedTitle.value.includes(word)
    )
)

const editHasBadWord = computed(() =>
    bannedWords.some((word) =>
        normalizedEditTitle.value.includes(word)
    )
)

const duplicate = computed(() =>
    props.labels.some((label) =>
        label.title.toLowerCase().trim() ===
        normalizedTitle.value
    )
)

const editDuplicate = computed(() =>
    props.labels.some((label) =>
        label.id !== editingId.value &&
        label.title.toLowerCase().trim() ===
        normalizedEditTitle.value
    )
)

const canSubmit = computed(() =>
    title.value.trim().length >= 2 &&
    !titleTooLong.value &&
    !hasBadWord.value &&
    !duplicate.value
)

const canUpdate = computed(() =>
    editTitle.value.trim().length >= 2 &&
    !editTitleTooLong.value &&
    !editHasBadWord.value &&
    !editDuplicate.value
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

function startEdit(label) {
    editingId.value = label.id
    editTitle.value = label.title
    editColor.value = label.color
}

function cancelEdit() {
    editingId.value = null
    editTitle.value = ''
    editColor.value = '#3b82f6'
}

function updateLabel(label) {
    if (!canUpdate.value) {
        return
    }

    router.put(`/settings/labels/${label.id}`, {
        name: editTitle.value.trim(),
        color: editColor.value,
    }, {
        preserveScroll: true,
        onSuccess: cancelEdit,
        onError: (errors) => {
            console.log(errors)
        },
    })
}

function deleteLabel(label) {
    if (!confirm(`Delete label "${label.title}"?`)) {
        return
    }

    router.delete(`/settings/labels/${label.id}`, {
        preserveScroll: true,
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
                                class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4"
                            >
                                <div
                                    v-if="editingId === label.id"
                                    class="space-y-3"
                                >
                                    <div>
                                        <input
                                            v-model="editTitle"
                                            type="text"
                                            maxlength="20"
                                            class="w-full rounded-xl border bg-zinc-900 px-4 py-2 text-white outline-none placeholder:text-zinc-500"
                                            :class="
                                                editTitleTooLong
                                                    ? 'border-red-500/50 focus:border-red-500'
                                                    : 'border-zinc-800 focus:border-zinc-600'
                                            "
                                        />

                                        <div class="mt-2 flex items-center justify-between gap-3">
                                            <div>
                                                <p
                                                    v-if="editTitleTooLong"
                                                    class="text-sm font-medium text-red-400"
                                                >
                                                    Title cannot be longer than 20 characters.
                                                </p>

                                                <p
                                                    v-if="editHasBadWord"
                                                    class="text-sm font-medium text-red-400"
                                                >
                                                    This label contains a blocked word.
                                                </p>

                                                <p
                                                    v-if="editDuplicate"
                                                    class="text-sm font-medium text-red-400"
                                                >
                                                    This label already exists.
                                                </p>
                                            </div>

                                            <span
                                                class="ml-auto shrink-0 text-xs font-semibold"
                                                :class="
                                                    editTitleCharactersLeft <= 5
                                                        ? 'text-red-400'
                                                        : 'text-zinc-500'
                                                "
                                            >
                                                {{ editTitleCharactersLeft }}/20
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex gap-3">
                                        <input
                                            v-model="editColor"
                                            type="color"
                                            class="h-10 w-14 cursor-pointer rounded-xl border border-zinc-800 bg-zinc-900 p-1"
                                        />

                                        <input
                                            v-model="editColor"
                                            type="text"
                                            class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-2 text-white outline-none focus:border-zinc-600"
                                        />
                                    </div>

                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            :disabled="!canUpdate"
                                            class="rounded-lg bg-white px-3 py-2 text-sm font-bold text-black disabled:cursor-not-allowed disabled:opacity-40"
                                            @click="updateLabel(label)"
                                        >
                                            Save
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-lg bg-zinc-800 px-3 py-2 text-sm font-bold text-white hover:bg-zinc-700"
                                            @click="cancelEdit"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </div>

                                <div
                                    v-else
                                    class="flex items-center justify-between gap-4"
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

                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-zinc-500">
                                            {{ label.color }}
                                        </span>

                                        <button
                                            type="button"
                                            class="rounded-lg bg-zinc-800 px-3 py-1.5 text-xs font-bold text-white hover:bg-zinc-700"
                                            @click="startEdit(label)"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-lg bg-red-500/10 px-3 py-1.5 text-xs font-bold text-red-400 hover:bg-red-500/20"
                                            @click="deleteLabel(label)"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
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
                                    maxlength="20"
                                    placeholder="e.g. Cozy games"
                                    class="w-full rounded-xl border bg-zinc-950 px-4 py-3 text-white outline-none placeholder:text-zinc-500"
                                    :class="
                                        titleTooLong
                                            ? 'border-red-500/50 focus:border-red-500'
                                            : 'border-zinc-800 focus:border-zinc-600'
                                    "
                                />

                                <div class="mt-2 flex items-center justify-between gap-3">
                                    <div>
                                        <p
                                            v-if="titleTooLong"
                                            class="text-sm font-medium text-red-400"
                                        >
                                            Title cannot be longer than 20 characters.
                                        </p>

                                        <p
                                            v-if="hasBadWord"
                                            class="text-sm font-medium text-red-400"
                                        >
                                            This label contains a blocked word.
                                        </p>

                                        <p
                                            v-if="duplicate"
                                            class="text-sm font-medium text-red-400"
                                        >
                                            This label already exists.
                                        </p>
                                    </div>

                                    <span
                                        class="ml-auto shrink-0 text-xs font-semibold"
                                        :class="
                                            titleCharactersLeft <= 5
                                                ? 'text-red-400'
                                                : 'text-zinc-500'
                                        "
                                    >
                                        {{ titleCharactersLeft }}/20
                                    </span>
                                </div>
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